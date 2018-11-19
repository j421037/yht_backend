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
use Illuminate\Http\Request;
use App\Http\Resources\ARSumResource;
use App\Http\Resources\ARSumRoleResource;

class ARSumController extends Controller
{

    /**以客户为主体**/
    public function query(Request $request)
    {
        $customer = new RealCustomer();
        $customer = $customer->with(['project'])->limit(10)->get();
         
        $list = [];
        $i = 0;
        $index = 1;

        foreach($customer as $k => $v) {

            $cid = $v->id;
            $project = $v->project;

            unset($v->project);
            unset($v->id);
            unset($v->user_id);

            $list[$i] = $v;
            $list[$i]->nameshow = true;
            $list[$i]->cid = $cid;
            $list[$i]->index = $index;
            
            if ($project) {

                $first_project = true;

                foreach ($project as $kk => $vv) {
                    //如果当前的项目是当前客户的第一个项目，则例外处理
                    if ($first_project) {
                        $list[$i]->pid = $vv->id;
                        $list[$i]->project = $vv->name;
                        $list[$i]->user_id = $vv->user_id;
                        $list[$i]->tid     = $vv->tid;
                        $list[$i]->tag     = $vv->tag;
                        $list[$i]->estimate = $vv->estimate;
                        $list[$i]->affiliate = $vv->affiliate;
                        $list[$i]->agreement = $vv->agreement;
                        $list[$i]->attachment_id = $vv->attachment_id;
                        $first_project = false;
                        continue;
                    }
                    
                    ++$i;
                    ++$index;
                    $proname = $vv->name;
                    $pid = $vv->id;

                    unset($vv->name);
                    unset($vv->id);

                    $list[$i] = $vv;
                    $list[$i]->project = $proname;
                    $list[$i]->nameshow = false; 
                    $list[$i]->index = $index;  
                    $list[$i]->cid = $cid;
                    $list[$i]->pid = $pid; 
                    $list[$i]->name = $v->name;
                }
            }
            
            ++$i;
            ++$index;
        }

        $list = collect($list);

        return response(['data' => ARSumResource::collection($list)], 200);
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
    public function role($flag = false)
    {	
    
    	$userId = Auth::user()->id;
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

        if ($flag) {

            return $result;

        } else {

            return response($result, 200);
        }
    }


}
