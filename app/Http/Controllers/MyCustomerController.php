<?php
/**
* 个人客户管理类
* 2018-06-20
*/
namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Customer;
use App\CustomerNote;
use App\CustomerFree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use App\Http\Resources\PersonalCustomerResource;

class MyCustomerController extends Controller
{
    public function test(Request $request) 
    {
        $data = User::find(Auth::user()->id)->customer;

        return response(PersonalCustomerResource::collection($data)->sortByDesc('sort'));
    }
    /**
    * 个人客户列表
    */
    public function list(Request $request)
    {
        // return $request->offset;
        // DB::enableQueryLog();
        $list = User::find( Auth::user()->id)
                        ->customer()
                        ->offset($request->offset)
                        ->limit($request->limit)
                        ->groupBy('id')
                        ->groupBy('user_id')
                        ->orderBy('updated_at', 'desc')
                        ->get();

        // $data = PersonalCustomerResource::collection($data)->sortByDesc('sort')->values()->all();
        $data = PersonalCustomerResource::collection($list);

        $count = Customer::where(['user_id' => Auth::user()->id])->count();
        
        $accept = Customer::where(['user_id' => Auth::user()->id, 'accept' => 1])->count();

        $loadAll = false;

        $nextOne = $request->offset + $request->limit;

        $next = Customer::where(['user_id' => Auth::user()->id])->select(['id','name','demand'])->offset($nextOne)->limit($request->limit)->orderBy('id', 'desc')->get();

        if ( count($list) < 1 || count($next) < 1) {

            $loadAll = true;
        } 


    	return response(['data' => $data,'accept' => $accept, 'count' => $count,'loadAll' => $loadAll, 'limit' => $request->limit, 'offset' => $request->offset], 200);
    }

    /**
    * 领取一个客户资源
    */
    public function store(Request $request) 
    {
    	
    	$customer = Customer::find($request->id);

    	$userCustomerCount = User::find( Auth::user()->id)->customer()->count();//领取的客户数

        //publish == 1 表示已公开
    	if (Empty($customer->user_id) && $customer->publish == 1) {

    		$customer->user_id = Auth::user()->id;

            DB::beginTransaction();

    		if ($customer->save() && CustomerNote::create(['user_id' => $customer->user_id, 'customer_id' => $customer->id, 'action' => 0])) {

                DB::commit();

                return response(['status' => 'success'], 200);
            }

    		DB::rollback();

            return response(['status' => 'fail','error' => '领取失败'], 200);
    	}

    	return response(['status' => 'fail', 'error' => '该客户资源已分配给其他人'], 200);
    }

    /**
    * 释放一个客户资源
    *@param 已经验收的客户不能再次释放
    *@param $customer_id
    */
    public function free(Request $request) 
    {

        DB::beginTransaction();

        try {

            $result = Customer::where(['id' => $request->id, 'user_id' => Auth::user()->id, 'accept' => 0])->update(['user_id' => null]);

            $log = CustomerNote::create(['user_id' => Auth::user()->id, 'customer_id' => $request->id, 'action' => 2]);

            //释放理由
            $reason = CustomerFree::create(['customer_id' => $request->id,'user_id' => Auth::user()->id, 'reason' => $request->reason]);

            if ($result != false && $log != false && $reason != false) {
                DB::commit();
                return response(['status' => 'success'], 200);
            }

        } catch(\Illuminate\Database\QueryException $e) {

            return response(['status' => 'fail', 'error' => $e->getMessage()], 200);
        }
    }

    /**
    *验收一个客户
    *@param 客户必须被当前用户领取了才能验收
    */
    public function accept(Request $request)
    {
        DB::beginTransaction();

        try {

            $result = Customer::where(['user_id' => Auth::user()->id, 'id' => $request->id])->update(['accept' => 1]);
            //添加领取日志
            $log = CustomerNote::create(['user_id' => Auth::user()->id, 'customer_id' => $request->id, 'action' => 1]);

            if ($result != false && $log != false) {
                DB::commit();
                return response(['status' => 'success'], 200);
            }

        } catch(\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response(['status' => 'fail', 'error' => $e->getMessage()], 200);
        }
        
    }
}
