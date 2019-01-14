<?php
/**
 * 论坛部门文章分类
 */
namespace App\Http\Controllers;

use App\User;
use App\Article;
use App\ArticleCategory;
use App\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    protected $article;

    public function __construct(Mapping $mapping, Department $department, User $user, ArticleCategory $category, Article $article)
    {
        $this->user = $user;
        $this->mapping = $mapping;
        $this->category = $category;
        $this->department = $department;
        $this->article = $article;
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

    //只能删除当前部门的分类

    public function del(Request $request)
    {
        try {
            DB::beginTransaction();

            $category = $this->category->find($request->id);

            if (!$category) {
                throw new \Exception('该分类不存在');
            }

            //管理员
            if ($this->isAdmin()) {
                $result = $category->delete();
            }
            else if ($this->isManager()) {
                //经理 判断分类的权限
                $mapping = $this->mapping->find($category->module_id);
                $department = $this->department->find($mapping->sid);

                if ($department->user_id == $this->getUserId()) {
                    $result = $category->delete();
                }
            }
            else {
                throw new \Exception('没有权限操作该内容');
            }

            if ($result) {
                //当前分类文章转移到默认分类
                $this->article->where(['category_id' => $request->id])->update(['category_id' => 0]);
                DB::commit();

                return response(['status' => 'success'], 200);
            }

        }
        catch (QueryException $e) {
            DB::rollback();
            $errmsg =  $e->getMessage();
        }
        catch (\Exception $e) {
            $errmsg =  $e->getMessage();
        }

        return response(['status' => 'error','errmsg' => $errmsg]);
    }
}
