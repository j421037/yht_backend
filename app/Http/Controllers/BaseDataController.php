<?php

/**
* 基础资料控制器类
*/

namespace App\Http\Controllers;

use App\BaseData;
use Illuminate\Http\Request;

class BaseDataController extends Controller
{

	public function list()
	{
		return response(['data' => BaseData::all()], 200);
	}

	/**获取icon文件地址**/
	public function getIcon()
	{
		$icon = BaseData::where(['name' => 'icon_url'])->first();

		return response(['data' => $icon], 200);
	}

	/**更新图标文件地址**/
    public function updateIconUrl(Request $request)
    {
    	try {

	    	$base = BaseData::updateOrCreate(
		    		['name' => 'icon_url'], 
		    		$request->post()
		    	);

	    	if ($base) {
	    		return response(['status' => 'success'], 200);
	    	}
	    } catch (\Excetion $e) {
	    	return response(['status' => 'fail', 'error' => $e->getMessage()], 200);
	    }
    }

    /**
    * 基础分页配置
    */
    public function pagination()
    {
        $config = [
            'pagesize'  => 5, //默认分页的数量
            'pagesizes' => [5,10,15,20,30,50],//分页数量
            'layout'    => 'total, sizes, prev, pager, next, jumper',//分页显示的功能
        ];

        return response(['data' => $config], 200);
    }
}
