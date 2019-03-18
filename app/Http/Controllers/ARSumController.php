<?php
/**
* 应收款汇总
* @author 2018-09-19
* @date 10-29 改变需求
*/
namespace App\Http\Controllers;

use Auth;
use App\Role;
use App\User;
use App\Department;
use App\RealCustomer;
use App\AReceivebill;
use App\AReceivable;
use App\Project;
use App\FilterProgram;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Resources\ARSumResource;
use App\Http\Resources\ARSumRoleResource;
use Illuminate\Support\Facades\DB;

class ARSumController extends Controller
{
    /**
     * 返回客户+项目信息
     * 默认情况下，个人返回自己创建的项目，部门经理、助理返回当前部门的项目； boss 返回全部的项目信息
     */
    public function query(Request $request)
    {
       // DB::enableQueryLog();
        //第一次请求
        $filter = FilterProgram::where(['default' => 1, 'user_id' => $this->getUserId()])->first();

        if ($request->initialization == true && isset($filter->conf) ) {
            $filter = (object)json_decode($filter->conf, true);
        }
        //多次请求
        else {
            $filter = (object) $request->conf;
        }

        $AuthList = $this->AuthIdList();
        $model = new Project();
        $result = $model->ARSum($filter, $AuthList, $request->offset, $request->limit);
        $count = $result['total'];
        $data = $result['row'];
        $year = date('Y', time());
        $currentCid = 0;
        $project = 0;
        $tid = 0;

        array_walk($data, function(&$item, $index) use (&$currentCid, &$project, &$tid, $year) {

            if ($item->cid == $currentCid) {
                $item->nameshow = false;
            }
            else {
                $item->nameshow = true;
                $currentCid = $item->cid;
            }

            if ($item->project == $project && $item->tid == $tid) {
                $item->projectshow = false;
            }
            else {
                $item->projectshow = true;
                $project = $item->project;
                $tid = $item->tid;
            }

            $item->year = $year;
            $item->index = $index + 1;
        });

        $data = collect($data);

        return response(['status' => 'success', 'data' => ARSumResource::collection($data), 'total' => $count], 200);
    }

    /**以项目为主体**/
    public function query1(Request $request)
    {

    	$Project = new Project();

    	$order = explode('_', $request->order);

    	$ARSum = $Project->buildARSum($request);

    	$result = collect($ARSum['data']);

    	$list = ARSumResource::collection($result);

        
    	//按欠款月数倒序排序
    	if (strtolower($order[0]) == 'months') {

            $list = json_decode(json_encode(response($list)->getOriginalContent()),true);

    		if (strtolower($order[1]) == 'desc') {

	    		$list = collect($list)->sortByDesc('continueReceivable')->values()->all();

	    	} else {
                
                $list = collect($list)->sortBy('continueReceivable')->values()->all();
            }

    	}

    	$summaries = $ARSum['summaries'][0];
    	$summaries->balance = number_format(bcsub(bcsub(bcsub($summaries->amountfor, $summaries->real_amountfor, 2),$summaries->discount, 2),$summaries->refund,2), 2);

        $summaries->amountfor = number_format($summaries->amountfor,2);
        $summaries->real_amountfor = number_format($summaries->real_amountfor,2);
        $summaries->discount = number_format($summaries->discount, 2);
        $summaries->refund = number_format($summaries->refund, 2);

    	return response(['data' => $list, 'total' => $ARSum['total'], 'summaries' => $summaries], 200);
    }

    /**处理权限信息
    * 如果是部门或者助理，则可以返回部门员工列表
    * @param $flag 返回数组或response 
    */
    public function role()
    {

    	$userId = $this->getUserId();
    	$userCollect = User::with(['role'])->where(['id' => $userId])->get();
    	$user = $userCollect[0];
    	$role = $user->role->pluck('name')->toArray();
		$userList = $userCollect;
		$hasRole = false;
    	
    	$department =  Department::where(['user_id' => $user->id])->first();
    	//超级管理员
    	if ( $user->group == 'admin' ) {

    		$userList = User::all();
    		$hasRole = true;

            //部门助理
    	}  else if ($department || in_array('部门助理', $role)) {

    		$userList = User::where(['department_id' => $user->department_id])->get();
    		$hasRole = true;
    	}

    	$list = ARSumRoleResource::collection($userList);
        $result = ['user' => $list, 'hasRole' => $hasRole];

        return response($result, 200);
    }

    /**调试的情况下清空表**/
    public function initialization()
    {
        try {
            $tables = ['a_receivables', 'a_receivebills', 'projects','real_customers', 'receivable_plans','refunds', 'potential_customers','potential_projects'];

            foreach ($tables as $v) {
                DB::select('truncate table '. $v);
            }

            return response(['status' => 'success'], 200);
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }

    //同步金蝶的销售订单
    public function SyncKingdeeSaleOrder()
    {
        $url = "http://kingdee.miaoyi09.com/K3Cloud/YHT.WebAPI.ServiceExtend.ServicesStub.SaleOrderService.ExecuteBillQuery.common.kdsvc";
        $params = [
            'AcctId'    => "5c6f64cf1215e5", //账套ID 必须
            "UserName"  => "kingdee",        //金蝶用户名 必须
            "Password"  => "sb123.++",        // 金蝶用户名密码 必须
            "PageSize"  => "10",
            "PageNow"   => "1"
        ];

        $kingdee = $this->CURL($url, json_encode($params), "POST");

        var_dump(json_decode($kingdee, true));
    }
}
