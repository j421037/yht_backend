<?php
/**
* 属性绑定控制器
* @author 王
* @date 2018-11-06
*/
namespace App\Http\Controllers;

use App\BindAttr;
use App\EnumberateItem;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\BindAttrResource;
use App\Http\Resources\BindAttrOneResource;
use App\Http\Resources\EnumberateItemResource;
use App\Http\Resources\EnumberateItemToFormResource;
use Illuminate\Database\QueryException;
use App\Http\Requests\BindAttrStoreRequest;
use App\Http\Requests\BindAttrOneRequest;

class BindAttrController extends Controller
{
    public function list(Request $request)
    {
    	$list = BindAttr::all();

    	return response(BindAttrResource::collection($list), 200);
    }

    public function store(BindAttrStoreRequest $request)
    {
    	try {

    		$data = new \StdClass;

    		$data->name = $request->name;
    		$data->key = $request->key;
    		$data->eid = $request->enumberate_id;
    		$data->pid = $request->module_id;

    		if (BindAttr::create((array)$data)) {
    			return response(['status' => 'success']);
    		}
    		else {
    			return response(['status' => 'error', 'errmsg' => "添加失败！"]);
    		}
    	} catch (QueryException $e) {
    		return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
    	}
    }

    /**
    * 返回当前路径下所有的属性绑定信息
    */
    public function one(BindAttrOneRequest $request)
    {
    	$attr = Permission::where(['front_path' => $request->path])->first()->BindAttr;

    	$list = [];

    	foreach ($attr as $v) {
    		$list[$v->key] = EnumberateItemToFormResource::collection(EnumberateItem::where(['eid' => $v->eid, 'disable' => 0])->orderBy("index", "asc")->get());
    	}

    	return response($list);
    }

    public function update(BindAttrStoreRequest $request)
    {
    	if ($request->id) {

    		$model = BindAttr::find($request->id);

    		try {
    			$model->name = $request->name;
    			$model->key = $request->key;
    			$model->eid = $request->enumberate_id;
    			$model->pid = $request->module_id;

    			if ($model->save()) {
    				return response(['status' => 'success']);
    			}
    		}
    		catch (QueryException $e) {
	    		return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
	    	}
    	}
    }
}
