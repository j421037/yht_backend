<?php
/**
* 移动端基础数据控制器类
*2018 7-20
*@author: 王鑫
*/
namespace App\Http\Controllers;

use App\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\MobileNavigationResource;
// use App\Http\Resources\NavigationResource;

class MobileBaseController extends UserBaseController
{
	/**初始化**/
    public function init()
    {
    	$list = $this->_userNavigation(true);
 		$navigation = MobileNavigationResource::collection($list)->sortBy('mobile_sort')->values()->all();

    	return response(['navigation' => $navigation], 200);
    }
}
