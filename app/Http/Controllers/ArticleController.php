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
    public function index(Request $request)
    {
        
        $list = ArticleResource::collection($this->ArticleQuery());

        return response($list, 200);
    }

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
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        //处理文章缩略图
        if (!empty($request->titlepic)) {
            $file = Storage::disk('public')->putFile(date('Y-m-d', time()),$request->titlepic);
            $data['titlepic'] = $file;
        }

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

        return response($list, 200);
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
        $article = Article::select('id','title','body', 'status','category_id')
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

    /**
    * 封装查询
    */
    protected function ArticleQuery($userId = null)
    {
        $where = ['articles.status' => 1,'articles.deleted_at' => null];

        if ($userId) {
            $where['articles.user_id'] = $userId;
        }
        $list = DB::table('articles')
                    ->select(
                        'articles.id',
                        'articles.title', 
                        'articles.user_id',
                        'articles.category_id',
                        'articles.created_at',
                        'articles.updated_at',
                        'article_datas.agrees', 
                        'article_datas.comments',
                        'article_datas.isFine'
                    )
                    // ->where(['articles.status' => 1])
                    ->where($where)
                    ->join('article_datas', 'articles.id', '=', 'article_datas.article_id')
                   
                    ->orderBy('article_datas.isFine', 'desc')
                    ->orderBy('articles.updated_at', 'desc')
                    ->get();
        return $list;
    }
}
