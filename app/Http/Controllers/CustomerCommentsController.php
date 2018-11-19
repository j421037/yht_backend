<?php

/**
* 客户跟进数据类
*/
namespace App\Http\Controllers;

use Auth;
use App\Customer;
use App\CustomerPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CommentsRequest;
use App\Http\Resources\CustomerCommentsResource;

class CustomerCommentsController extends Controller
{
    /**
    * 新建跟进记录
    *
    */
    public function store(CommentsRequest $request)
    {
    	$data = $request->all();

    	$data['user_id'] = Auth::user()->id;

    	/**当前用户id 必须要和 客户表中对应的用户id一致**/

    	if (Customer::find($data['customer_id'])->user_id != $data['user_id'] ) {

    		return response(['status' => 'fail', 'error' => '非法操作, 您无权修改该客户'], 200);
    	}


    	try {

	    	if (CustomerPost::create($data)) {

	    		return response(['status' => 'success'], 200);
	    	}

	    } catch(\Illuminate\Database\QueryException $e) {

	    	return response(['status' => 'fail', 'error' => $e->getMessage(), 'sql' => DB::getQueryLog()], 200);
	    }
    }

    /**
    * 返回当前客户的跟进记录
    *@param $customer_id
    */
    public function comments(Request $request)
    {

    	$list = CustomerPost::where(['customer_id' => $request->id])
                            ->limit($request->limit)
                            ->offset($request->offset)
                            ->orderBy('id', 'desc')
                            ->get();

        $loadAll = false;

        $nextOne = $request->offset + $request->limit;

        $next = CustomerPost::where(['customer_id' => $request->id])
                            ->limit($request->limit)
                            ->offset($request->offset)
                            ->count();


        if ( count($list) < 1 || $next < 1) {

            $loadAll = true;
        } 


    	return response(['data' => CustomerCommentsResource::collection($list), 'loadAll' => $loadAll], 200);
    }
}
