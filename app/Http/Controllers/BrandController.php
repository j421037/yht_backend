<?php

namespace App\Http\Controllers;

use App\Brand;
use Miao\Miao;
use Illuminate\Http\Request;
use App\Http\Requests\BrandFormRequest;
use App\Http\Resources\BrandResource;

class BrandController extends Controller
{
	protected $miao;


    /**
    * 添加品牌
    */
    public function store(BrandFormRequest $request) 
    {
    	if (Brand::create($request->all())) {
    		return response(['status' => 'success'],200);
    	}

    	return response(['status' => 'fail'],200);
    } 

    /**
    * 返回品牌列表
    */
    public function all(Request $request)
    {
    	return response(['data' => BrandResource::collection(Brand::all())]); 
    }

    /**
    * 更新品牌
    */
    public function update(BrandFormRequest $request)
    {
    	$brand = Brand::find($request->post('id'));
    	
    	try {
	    	if ($brand->update($request->all())) {
	    		return response(['status' => 'success'], 200);
	    	}
	    } catch (App\Exceptions\ApiException $e) {
	    	return response(['status' => 'fail', 'msg' => $result], 200);
	    }
    }

    /**
    * 更新品牌状态
    */
    public function updateStatus(Request $request) 
    {
    	$brand = Brand::find($request->post('id'));

    	try {
	    	if ($brand->update(['status' => $request->post('status')])) {
	    		return response(['status' => 'success'], 200);
	    	}
	    } catch (App\Exceptions\ApiException $e) {
	    	return response(['status' => 'fail', 'msg' => $result], 200);
	    }
    }

}
