<?php
/**
*论坛基础控制器类
**/
namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Role;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\ForumMenuResource;

class ForumController extends Controller
{
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

        }])->whereIn('id', $perId)->where(['pid' => $forumId])->orderBy('pc_sort')->get();

        $list = ForumMenuResource::collection($list);

        return response($list, 200);
    }
}
