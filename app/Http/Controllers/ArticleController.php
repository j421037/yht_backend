<?php

namespace App\Http\Controllers;

use App\Department;
use Auth;
use Storage;
use App\User;
use App\Article;
use App\ArticleCategory;
use App\ArticleData;
use App\ArticleAgree;
use App\ArticleNotify;
use App\Events\ArticleComment;
use App\ForumModuleMappingDepartment AS FMapping;
use Illuminate\Http\Request;
use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\ArticleContentResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ArticleController extends Controller
{
    private $mapping;
    private $article;
    private $category;

    public function __construct(FMapping $mapping, Article $article, ArticleCategory $category)
    {
        $this->mapping = $mapping;
        $this->article = $article;
        $this->category = $category;
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
        $data = [];
        $data['title'] = $request->title;
        $data['user_id'] = $this->getUserId();

        //文章属性
        //私有
        if ($request->attr == 'protected') {
            $data['attr'] = 'protected';
        }
        else {
            //公开
            $data['attr'] = 'public';
        }

        //模块
        $data['module_id'] = $request->module_id;


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
        $data['category_id'] = $request->category;

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
            $data = [];
            $data['title'] = $request->title;
            $data['body'] = $request->body;
            $data['category_id'] = $request->category;
            $data['attr'] = $request->attr;
            //文章第一个图片作为文章缩略图
            if ($titlepic = $this->FindTitlePic($request->body)) {
                $data['titlepic'] = $titlepic;
            }
            //文章摘要
            $str = $request->body;
            $str = strip_tags($str);
            $str = preg_replace('/(\&ldquo\;|\&rdquo\;)/','',$str);
            $data['abstract'] = mb_substr($str, 0, 103,'utf-8').'...';
            //是否发布文章
            if ($request->status == 1) {
                $data['status'] = 1;
            }

            $article = Article::where(['id' => $request->id, 'user_id' => $this->getUserId()])->update($data);

            if ($article) {
                return response(['status' => 'success'], 200);
            }

        } catch(\Illuminate\Database\QueryException $e) {

            return response(['status' => 'error', 'error'=>$e->getMessage()], 200);
        }
    }

    /**
     *修改文章分类,只限于本部门的分类
     */
    public function UpdateCategory(Request $request)
    {
        try {
            if ($this->isAdmin() || $this->isManager()) {
                $article = $this->article->find($request->article_id);
                $module = $this->mapping->find($article->module_id);
                //当前模块下所有的分类
                $categories = $this->category->where(['module_id' => $module->id])->get()->pluck('id');

                if ($categories->contains($request->category_id)) {
                    $article->category_id = $request->category_id;
                    $article->save();

                    return response(['status' => 'success'], 200);
                }
                else {
                    throw(new \Exception('分类不存在'));
                }

            }
            else {
                throw(new \Exception('没有权限'));
            }
        }
        catch (QueryExpetion $e) {
            return reponse(['status' => 'error', 'errmsg' => $e->getMessage()], 202);
        }
        catch (\Exception $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()], 202);
        }
    }

    public function delete(Request $request)
    {
        $article = Article::where(['id' => $request->id,'user_id' => $this->getUserId()])->first();

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
        $article = Article::where(['id' => $request->id, 'user_id' => $this->getUserId()])->first();

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
        $article = Article::select('id','title','body', 'status','attr','module_id','category_id')
                            ->where(['id' => $request->id, 'user_id' => Auth::user()->id])
                            ->first();

        if ($article)
            return response(['status' => 'success','data' => $article], 200);
        else
            return response(['status' => 'error', 'errmsg' => '无权修改该文章'],202);
    }

    /**已发布文章列表**/
    public function PublishList(Request $request)
    {
        $list = Article::with(['ArticleData'])->where(['user_id' => $this->getUserId()])->orderBy('id','desc')->get();
        $list = ArticleResource::collection($list);

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

    //获取用户对应的模块id
    protected function GetModuleId($moduleName = null)
    {
        $orm = $this->mapping;
        //如果通过名称的方式查询， 只能查询到属性为公开的模块
        if ($moduleName) {
            $name = $moduleName;
            $orm->where(['attr' => 'public']);
        }
        else {
            $user = User::find($this->getUserId());
            $department = Department::find($user->department_id);
            $name = $department->name;
        }

        $module = $orm->where(['name' => $name])->first();

        return $module->id;
    }

}
