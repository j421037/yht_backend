<?php

namespace App\Http\Controllers;

use Auth;
use Storage;
use App\User;
use App\Article;
use App\ArticleData;
use App\ArticleAgree;
use App\ArticleNotify;
use App\Events\ArticleComment;
use Illuminate\Http\Request;
use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\ArticleContentResource;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function show($id)
    {
        $article = Article::find($id); 

        if ($article) {
            $list = new ArticleContentResource($article);
            return response($list, 200);
        }

        return response(['status' => 'error', 'error' => '文章不存在'], 404);
    }

    public function store(ArticleRequest $request)
    {
        $data = [];
        $data['title'] = $request->title;
        $data['user_id'] = Auth::user()->id;

        //文章属性
        //公开
        if ($request->attr == 'public') {
            $data['attr'] = 'public';
            $data['module_id'] = $request->module_id;
        }
        else {
            //私有
            $data['attr'] = 'protected';
            $data['module_id'] = User::find($this->getUserId())->department_id;
        }

        //文章第一个图片作为文章缩略图
        if ($titlepic = $this->FindTitlePic($request->body)) {
            $data['titlepic'] = $titlepic;
        }

        $data['body'] = $request->body;
        $str = $request->body;
        $str = strip_tags($str);
        $str = preg_replace('/(\&ldquo\;|\&rdquo\;)/','',$str);
        $data['abstract'] = mb_substr($str, 0, 103,'utf-8').'...';
        $data['status'] = $request->status;

        try {
            DB::beginTransaction();

            $article = Article::create($data);
            $articleData = ArticleData::create(['article_id' => $article->id]);

            if ($article && $articleData) {

                DB::commit();
                //分发事件
                event(new ArticleComment($article->id));

                return response(['status' => 'success'], 201);
            }

            DB::rollback();

        } catch(\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response(['status' => 'error', 'error' => $e->getMessage()], 200);
        }
    }

    public function update(ArticleRequest $request)
    {
        try {

            $article = Article::where(['id' => $request->id, 'user_id' => Auth::user()->id])->update($request->all());

            if ($article) {
                return response(['status' => 'success'], 200);
            }

        } catch(\Illuminate\Database\QueryException $e) {

            return response(['status' => 'error', 'error'=>$e->getMessage()], 200);
        }
    }

    public function delete(Request $request)
    {
        $article = Article::where(['id' => $request->id,'user_id' => Auth::user()->id])->first();

        if (User::find(Auth::user()->id)->group == 'admin') {
            $article = Article::find($request->id);
        }

        try {
            DB::beginTransaction();
            //  副表的内容也要删除
            $articleData = ArticleData::where(['article_id' => $article->id]);

            //把点赞的记录同时也删除

            $agree = ArticleAgree::where(['article_id' => $article->id])->delete();

            //相关的动态也必须删除
            $notify = ArticleNotify::where(['article_id' => $article->id])->delete();

            if ($article->delete() && $articleData->delete()) {
                DB::commit();
                return response(['status' => 'success'], 200);
            }
        } catch(\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response(['status' => 'error', 'error'=>$e->getMessage()], 200);
        }

       
    }

    /**草稿箱**/
    public function draft(Request $request)
    {
        $data = Article::where(['status' => 0])->orderBy('id', 'desc')->get();
        $list = ArticleResource::collection($data);

        return response(['status' => 'success', 'data' => $list], 200);
    }

    /**草稿箱发布文章**/
    public function publish(Request $request) 
    {
        $article = Article::where(['id' => $request->id, 'user_id' => Auth::user()->id])->first();

        $article->status = 1;

        if ($article->save()) {

            return response(['status' => 'success'], 200);
        }
        return response(['status' => 'error'], 200);
    }

    /**
    *  返回需要修改的文章
    */
    public function ShowEdit(Request $request) 
    {   
        $article = Article::select('id','title','body', 'status','attr','module_id')
                            ->where(['id' => $request->id, 'user_id' => Auth::user()->id])
                            ->first();

        return response($article, 200);
    }

    /**已发布文章列表**/
    public function PublishList(Request $request)
    {
        $list = ArticleResource::collection($this->ArticleQuery(Auth::user()->id));

        return response($list, 200);
    }

    /**截取文章中第一个图片**/
    protected function FindTitlePic($body)
    {
        $pattern='/<img((?!src).)*src[\s]*=[\s]*[\'"](?<src>[^\'"]*)[\'"]/i';
        $titlepic = "";

        preg_match($pattern, $body,$matches);

        if (count($matches) > 0 && isset($matches['src'])) {
            $tmp = $matches['src'];
            //截取链接后的缩略图部分
            $tag = "?thumbimg=";
            $titlepic = substr($tmp,strpos($tmp,$tag)+strlen($tag));
        }

        return $titlepic;
    }
}
