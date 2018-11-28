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

    /**
     * 返回客户+项目信息
     * 默认情况下，个人返回自己创建的项目，部门经理、助理返回当前部门的项目； boss 返回全部的项目信息
     */
    public function query(Request $request)
    {
        //获取当前用户的所有项目
        $filter = $request;
        $projects = Project::with(['Customer'])->whereIn('user_id', $this->AuthIdList())->orderBy('cust_id')->get();
        $index = 1;
        $cid= null;

        $filter->year = date('Y', time());

        foreach ($projects as $k => $v) {
            $projectName = $v->name;
            $v->pid = $v->id;
            $v->cid = $v->customer->id;
            $v->name = $v->customer->name;
            $v->status = $v->customer->status;
            $v->project = $projectName;
            $v->index = $index;
            $v->year = $filter->year;

            if ($cid == $v->cust_id) {
                $v->nameshow = false;
            }
            else {
                $v->nameshow = true;
            }

            $cid = $v->cust_id;
            ++$index;
        }

        return response(['data' => ARSumResource::collection($projects)], 200);
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

    /**
     * 1、部门经理、助理 返回当前部门下所有用户的id
     */
    public function AuthIdList()
    {
        $user = User::find($this->getUserId());
        //获取用户的角色
        $userRoleName = User::find($user->id)->role->pluck('name');

        if ($userRoleName->contains("超级管理员")) {
            return User::all()->pluck('id');
        }
        else if (Department::where(['user_id' => $this->getUserId()])->first() || $userRoleName->contains("部门助理")) {
            return User::where(['department_id' => $user->department_id])->get()->pluck('id');
        }
        else {
            return collect($user->id);
        }
    }


}
