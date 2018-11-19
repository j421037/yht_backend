<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Role;
use App\Permission;
use Illuminate\Http\Request;

class UserBaseController extends Controller
{
    /**
    * 用户对应的导航
    * @param $moble 是否移动端
    * @return array
    */
    protected function _userNavigation($mobile = false)
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

        $where = [
        	'pid'	=> 0
        ];

        if ($mobile) {
        	$where['show_mobile'] = 1;
        } else {
            $where['show_pc'] = 1;
        }
        /**关联模型中约束子查询：
        * ORM::with(['xxx' => 闭包function() use( 可以传递变量) { 
        	//todo}
          ])
        **/
        $list = Permission::with(['children' => function($query) use ($perId, $mobile) {

            if ($mobile) {
        	   $query->whereIn('id', $perId)->where(['show_mobile' => 1]);

            } else {
               $query->whereIn('id', $perId)->where(['show_pc' => 1]);
            }

        }])->whereIn('id', $perId)->where($where)->get();

        return $list;
    }
}
