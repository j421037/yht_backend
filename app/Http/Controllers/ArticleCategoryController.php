<?php
/**
 * 论坛部门文章分类
 */
namespace App\Http\Controllers;

use App\User;
use App\ArticleCategory;
use App\Department;
use Illuminate\Http\Request;
use App\ForumModuleMappingDepartment as Mapping;
use App\Http\Requests\ArticleCategoryStoreRequest;
use App\Http\Resources\ArticleCategoryResource;
use App\Http\Resources\ArticleListCategoryResource;

class ArticleCategoryController extends Controller
{
    protected $user;
    protected $category;
    protected $mapping;
    protected $department;
    public function __construct(Mapping $mapping, Department $department, User $user, ArticleCategory $category)
    {
        $this->user = $user;
        $this->mapping = $mapping;
        $this->category = $category;
        $this->department = $department;
    }

    /**
    * module_id 返回该id 对应的分类
     */
    public function index(Request $request)
    {

        $module_id = $this->mapping->where(['sid' => $this->getDepartId()])->first()->id;
        $category = $this->category->with(['department'])->where(['module_id' => $module_id])->get();

        $list = ArticleCategoryResource::collection($category);

        return response(['status' => 'success','data' => $list], 200);
    }
    /**返回系统专区的分类**/
    public function feelback(Request $request)
    {
        $module = $this->mapping->where(['name' => '系统专区'])->first();
        $category = $this->category->where(['module_id' => $module->id])->get();

        return response(['status' => 'success', 'data' => ArticleCategoryResource::collection($category)], 200);
    }
    /**文章列表页获取分类**/
    public function ArticleListCategory(Request $request)
    {
        $default = new \StdClass;
        $default->id = 0;
        $default->name = '全部';
        $category = $this->category->where(['module_id' => $request->module_id])->get();
        $category->prepend($default);

        $list = ArticleListCategoryResource::collection($category);

        return response(['status' => 'success','data' => $list], 200);
    }
    public function manager(Request $request)
    {
        if ($this->isAdmin()) {
            $category = $this->category->with(['department'])->get();
        }
        else {
            $module_id = $this->mapping->where(['sid' => $this->getDepartId()])->first()->id;
            $category = $this->category->with(['department'])->where(['module_id' => $module_id])->get();
        }

    	$list = ArticleCategoryResource::collection($category);

    	return response($list, 200);
    }

    public function store(ArticleCategoryStoreRequest $request)
    {
    	try {
            $department = $this->department->where(['user_id' => $this->getUserId()])->first();

            if ($department) {
                $module_id = $this->mapping->where(['sid' => $department->id,'attr' => 'protected'])->first()->id;
            }
            else if ($this->isAdmin()) {
                $module_id = $request->module_id;
            }
            else {
                return response(['status' => 'error','errmsg' => '没有操作该功能的权限']);
            }

    	    $data = [
    	        'name'      => $request->name,
                'user_id'   => $this->getUserId(),
                'module_id' => $module_id
            ];

    		$ArticleCategory = ArticleCategory::create($data);

    		if ($ArticleCategory) {
    			return response(['status' => 'success'], 200);
    		}
    	} catch(\Illuminate\Database\QueryException $e) {
    		return response(['status' => 'error', 'error' => $e->getMessage()], 200);
    	}
    }

    public function update(ArticleCategoryStoreRequest $request)
    {
        try {
            $department = $this->department->where(['user_id' => $this->getUserId()])->first();

            //非部门管理并且也不是系统管理 则没有权限
            if (!$department && !$this->isAdmin()) {

                return response(['status' => 'error','errmsg' => '没有操作该功能的权限']);
            }
            //正常部门管理
            $category = ArticleCategory::find($request->id);
            $category->name = $request->name;

            //如果是系统管理 则可以跨模块修改
            if ($this->isAdmin()) {
                $category->module_id = (int) $request->module_id;
            }

            if ($category->save()) {
                return response(['status' => 'success'], 200);
            }

        } catch(\Illuminate\Database\QueryException $e) {
            return response(['status' => 'error', 'error' => $e->getMessage()], 200);
        }
    }
}
