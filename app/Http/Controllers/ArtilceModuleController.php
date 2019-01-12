<?php
/**
 * 论坛各模块下的文章处理
 * 1、当前模块下的用户 可以查看当前部门的所有文章
 * 2、非当前模块下的用户 可以查看当前部门公开的文章
 */

namespace App\Http\Controllers;

use App\User;
use App\Article;
use App\Department;
use App\ForumModuleMappingDepartment as FMapping;
use App\Http\Requests\ArticleModuleIndexRequest;
use App\Http\Requests\ArticleModuleSettopRequest;
use App\Http\Resources\ArticlePortalResource;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ArtilceModuleController extends Controller
{
    private $user;
    private $article;
    private $department;
    private $mapping;

    public function __construct(Article $article, Department $department, User $user, FMapping $mapping)
    {
        $this->user = $user;
        $this->article = $article;
        $this->department = $department;
        $this->mapping = $mapping;
    }

    /**
     * 返回各模块下文章列表
     * 当前模块对应的部门经理才有权限设置置顶
     */
    public function index(ArticleModuleIndexRequest $request)
    {
        //分页数据
        $pagenow = (int) $request->pagenow ?? 1;
        $limit = (int) $request->pagesize ?? 15;
        $offset = ($pagenow - 1) * $limit;
        //分页条件
        //通过判断当前请求的用户所在的部门，如果属于当前module_id对应的部门
        // 则可以获取当前部门所有文章
        $orm = $this->article->with(['ArticleData'])->where(['status' => 1,'module_id' => $request->module_id]);
        //文章分类
        if ($category = $request->category) {
            $orm->where(['category_id' => $category]);
        }

        //检查当前请求的模块
        $module = $this->mapping->find($request->module_id);
        $users = $this->user->where(['department_id' => $module->sid])->get()->pluck('id');
        //当前id不在部门列表内 则只显示公开信息
        if (!$users->contains($this->getUserId()) && $module->attr != 'public') {
            //返回当前部门的所有数据
            $orm = $orm->where(['attr' => 'public']);
        }

        $data = $orm->orderBy('top','desc')->orderBy('id','desc')->limit($limit)->offset($offset)->get();
        $count = count($data);
        $loaded = false;
        $hasRole = false;//判断设置置顶的权限

        //当前模块属性
        if ($module->attr == 'protected') {
            $department = $this->department->find($module->sid);
            //当前部门管理对应当前请求用户
            if($department->user_id == $this->getUserId()) {
                $hasRole = true;
            }
        }
        //超管除外
        if($this->isAdmin()) {
            $hasRole = true;
        }

        if ($count < $limit ) {
            $loaded = true;
        }

        return response(['status' => 'success','data' => ArticlePortalResource::collection($data),'loaded' => $loaded, 'hasRole' => $hasRole], 200);
    }
    //部门文章设置置顶
    public function SetTop(ArticleModuleSettopRequest $request)
    {
        try {
            if ($article = $this->ArticleCheckRole($request->id)) {
                $article->top = 1;
                $article->save();

                return response(['status' => 'success']);
            }
            else {
                return response(['status' => 'error', 'errmsg' => '无权操作该内容']);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
    //取消置顶
    public function CancelTop(ArticleModuleSettopRequest $request)
    {
        try {
            if ($article = $this->ArticleCheckRole($request->id)) {
                $article->top = 0;
                $article->save();

                return response(['status' => 'success']);
            }
            else {
                return response(['status' => 'error', 'errmsg' => '无权操作该内容']);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
    /**
     * 获取当前用户对当前文章的管理权限
     * @param $id 文章id
     * @return true 有管理权限  false 无权操作
     */
    private function ArticleCheckRole($id)
    {
        $article = $this->article->find($id);
        $module = $this->mapping->find($article->module_id);
        $department = $this->department->find($module->sid);
        // 当前用户是超级管理员或者部门经理
        if ($this->isAdmin() || ($module->attr == 'protected' && $department->user_id == $this->getUserId())) {
            return $article;
        }
        else {
            return false;
        }
    }
}
