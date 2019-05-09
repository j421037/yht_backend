<?php
/**
* 应收款汇总
* @author 2018-09-19
* @date 10-29 改变需求
*/
namespace App\Http\Controllers;

use App\Customer;
use App\Role;
use App\User;
use App\Department;
use App\RealCustomer;
use App\AReceivebill;
use App\AReceivable;
use App\Project;
use App\FilterProgram;
use App\Exports\ARSumExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Resources\ARSumResource;
use App\Http\Resources\ARSumRoleResource;
use Illuminate\Support\Facades\DB;

class ARSumController1 extends Controller
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

    /**
     * 导出项目欠款信息
     * 需要导出的内容：欠款金额
     * @param $pid 项目id
     * @return stream excel
     */
    public function ExportProjectArrears(Request $request)
    {
        if ($request->get('pid'))
            return "参数有误";

        $project = Project::find($request->pid);
        $customer = RealCustomer::find($project->cust_id);
        $sales = $this->ARBills(new AReceivable, $project->id)->toArray();
        $payments = $this->ARBills(new AReceivebill, $project->id)->toArray();

        $y = date("Y", time());//当前年
        $m = date('m', time());//当前月
        $begin = 0;
        $collect = [];

        //初始化今年的月份
        for($i = 1; $i <= $m; ++$i)
        {
            $collect[$i] = new \StdClass;
            $collect[$i]->year = $y;
            $collect[$i]->month = $i;
            $collect[$i]->begin = 0;
            $collect[$i]->sales= 0;
            $collect[$i]->payment = 0;
            $collect[$i]->arrears = 0;
            $collect[$i]->cust_name = $customer->name;
            $collect[$i]->name = $project->name;
        }

        array_walk($sales,function($item) use (&$y,&$collect,&$begin) {
            $item = (object) $item;

            if ($item->year < $y)
            {
                $begin += $item->amountfor;
            }
            else {
                $collect[(int)$item->month]->sales += $item->amountfor;
            }
        });

        array_walk($payments, function($item) use (&$y,&$collect, &$begin) {
            $item = (object) $item;

            if ($item->year < $y)
            {
                $begin -= $item->amountfor;
            }
            else {
                $collect[(int)$item->month]->payment += $item->amountfor;
            }
        });

        array_walk($collect, function(&$item) use (&$begin) {
            $item->begin = $begin;
            $item->arrears = bcadd(bcsub($item->sales, $item->payment),$item->begin);
            $begin = $item->arrears;
        });

        return Excel::download(new ARSumExport($collect),date('Y-m-d', time()).'.xlsx');
    }

    private function ARBills($model,$pid)
    {
        return $model->where(['pid' => $pid])
            ->selectRaw(" *, from_unixtime(date,'%Y') as `year`,from_unixtime(date,'%m') as `month` ")
            ->orderBy("date","asc")
            ->get();
    }
}
