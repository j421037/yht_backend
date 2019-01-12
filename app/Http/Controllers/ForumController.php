<?php
/**
*论坛基础控制器类
**/
namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Role;
use App\Permission;
use App\Department;
use App\ForumModuleMappingDepartment as Mapping;
use Illuminate\Http\Request;
use App\Http\Resources\ForumMenuResource;
use App\Http\Resources\ForumModuleAllResource;
use App\Http\Resources\PersonalModuleAndCategoryResource;

class ForumController extends Controller
{
    protected $department;
    protected $Fmodel;

    public function __construct(Department $department, Mapping $model)
    {
        $this->department = $department;
        $this->Fmodel = $model;
    }

    /**
	* 返回导航信息
	*/
    public function menu(Request $request)
    {
        
    	/** pluck 获取集合**/
        $roleId = User::find(Auth::user()->id)->role()->pluck('id');

    	$role = Role::whereIn('id', $roleId)->with(['permission'])->get();

        $list = $role->pluck('permission');
        
        $perId = [];

        foreach($list as $k => $v ) {
        	/**遍历出permission 功能对应的id**/
        	$perId = array_merge($perId, $v->pluck('id')->toArray());
        }

        /**关联模型中约束子查询：
        * ORM::with(['xxx' => 闭包function() use( 可以传递变量) { 
        	//todo}
          ])
        **/
        $forumId = Permission::where(['name' => '经验交流'])->first()->id;
        $list = Permission::with(['children' => function($query) use ($perId) {

            $query->whereIn('id', $perId);

        }])->whereIn('id', $perId)->where(['pid' => $forumId,'show_pc' => 1])->orderBy('pc_sort')->get();

        $list = ForumMenuResource::collection($list);

        return response($list, 200);
    }
    //返回所有版块
    public function AllModule()
    {
        $list = $this->Fmodel->orderBy('index','asc')->get();
        //合并版块信息
        $pub = new \StdClass;
        $pub->id = 0;
        $pub->name = '公共交流';
        $pub->attr = 'public';

        $list->prepend($pub);

        return response(['status' => 'success', 'data' => ForumModuleAllResource::collection($list)]);
    }
    /**发布文章时所需要的模块及对应的分类**/
    public function PersonalModuleAndCategory()
    {
        if (!$this->isAdmin()) {
            $mapping = $this->Fmodel->where(['name' => $this->getDepartName()])->orWhere(['attr' => 'public'])->orderBy('index','asc')->get();

            return response(['status' => 'success','data' => PersonalModuleAndCategoryResource::collection($mapping)]);
        }
    }
}
