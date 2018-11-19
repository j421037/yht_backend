<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Customer;
use App\CustomerNote;
use Illuminate\Http\Request;
use App\Http\Resources\InitCustomerResource;

class CustomerController extends Controller
{

    /**
    *+-----------------
    *+移动端客户组件数据初始化
    *+只显示 领取 释放 公开的状态
    *+-----------------
    */
    public function init(Request $request)
    {
    	$data = array();
    	
    	$list = CustomerNote::whereIn('action',[0,2,3])
    						->groupBy('customer_id','user_id','action')
    						->orderBy('updated_at', 'desc')
    						->limit($request->limit)
    						->offset($request->offset)
    						->get();	
    	//动态信息			
    	$data['data'] = InitCustomerResource::collection($list);
    	//客户数量
    	$data['count'] = Customer::where(['user_id' => Auth::user()->id ])->count();

    	return response($data, 200);
    }
}
