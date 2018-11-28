<?php

namespace App\Http\Resources;

use App\Receivable;
use App\User;
use App\ARType;
use App\AReceivebill;
use App\AReceivable;
use App\Refund;
use App\ReceivablePlan;
use App\EnumberateItem;
use App\Department;
use App\Http\Resources\ARDetailResource;
use App\Http\Resources\RefoundResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ARSumResource extends JsonResource
{
    const MonthMap = [
        "一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"
    ];
    
    private $receivable = 0;
    private $allRece = 0;
    private $receivableList = [];
    private $receivebill = 0;
    private $receivaebillList = [];
    private $initAmount;
    private $_continueReceivable;//持续欠款月数

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) 
    {
        return [
            'status'        => $this->status,
            'status_name'   => $this->_GetItem($this->status),
            'name'          => $this->name ,
            'nameshow'      => $this->nameshow,
            'project'       => $this->project ,
            'index'         => $this->index ,
            'user_name'     => $this->_userName($this->user_id) ,
            'tag'           => $this->tag ,
            'tax'           => $this->tax ,
            'payment_days'  => $this->payment_days,  
            'estimate'      => $this->estimate , //预估
            'affiliate'     => $this->affiliate , //挂靠信息
            'agreement'     => $this->agreement , //合同信息
            'attachment'    => $this->attachment_id ,//附件信息
            'protype'       => $this->_GetItem($this->tid), //施工范围 = 项目属性
            'rowkey'        => $this->_rowKey(),  
            'cust_id'       => $this->cid,
            'pid'           => $this->pid,
            'user_id'       => $this->user_id,  
            'has_init'      => $this->_checkInit($this->pid),
            'department'    => $this->_department($this->user_id),
            'init_data'     => $this->_getInit($this->pid, $this->year),
            'monthly_sales' => $this->_calcSale($this->pid), //计算每月销量
            'cooperation_amountfor'   => number_format($this->allRece),
        ];
    }

    /**计算期初
     * 例：假如说要查询2018年的数据，那就得返回2018年之前的数据作为2018的期初
     */
    private function _getInit($pid, $year)
    {
        $receivable = AReceivable::where(['pid' => $pid])->selectRaw(" *, from_unixtime(date,'%Y') as `year`,from_unixtime(date,'%m') as `month` ")->get();
        $receivebill = AReceivebill::where(['pid' => $pid])->selectRaw(" *, from_unixtime(date,'%Y') as `year`,from_unixtime(date,'%m') as `month` ")->get();

        //统计当前年之前的数据
        foreach ($receivable as $k => $v) {

            if ($v->year < $year || $v->is_init == 1) {
                $this->receivable += $v->amountfor;
            }
            else {
                array_push($this->receivableList, $v);
            }

            $this->allRece += $v->amountfor;
        }

        foreach ($receivebill as $k => $v) {
            if ($v->year < $year) {
                $this->receivebill += $v->amountfor;
            }
            else {
                array_push($this->receivaebillList, $v);
            }
        }

        $this->initAmount = $this->receivable - $this->receivebill;

        return $this->initAmount;
    }
    /**
     * 每月销量
     * @param $pid 项目id
     * @return array  每月销量 => [[name => 一月, receivable => 111, receivebill => 22, arrears => 89]]
     */
    protected function _calcSale($pid)
    {
        $list = [];
        //创建基础对象
        foreach (self::MonthMap as $k => $v) {
            $std = new \StdClass();
            $std->name = $v;

            if ($k == 0) {
                $std->initAmount = $this->initAmount;
            }
            else {
                $std->initAmount = 0;//期初
            }

            $std->amountfor = 0;//销售
            $std->real_amountfor = 0;//收款
            $std->arrears = 0;//欠款
            array_push($list,$std);
        }

        //统计销售额
        array_walk($this->receivableList, function($item) use (&$list) {
            $index = $item->month - 1;
            $list[$index]->amountfor = bcadd($item->amountfor, $list[$index]->amountfor, 2);
        });

        //统计回款额
        array_walk($this->receivaebillList, function($item) use (&$list) {
            $index = $item->month - 1;
            $list[$index]->real_amountfor = bcadd($item->amountfor, $list[$index]->real_amountfor, 2);
        });

        $temp = $list;
        //统计每月期初和期末欠款
        array_walk($temp, function(&$item, $index) use (&$list) {
            //期末欠款 = 期初 + 销售 - 收款
            $arrears = bcsub(bcadd($item->initAmount, $item->amountfor, 2),$item->real_amountfor,2);
            //格式化数字
            $item->arrears = number_format($arrears);
            $item->initAmount = number_format($item->initAmount);
            $item->amountfor = number_format($item->amountfor);
            $item->real_amountfor = number_format($item->real_amountfor);
            $next = $index + 1;

            if ($next < count($list)) {
                //下月期初 = 本月期末
                $list[$next]->initAmount =  $arrears;
            }
        });

        return $list;
    }

    /**获取枚举类型的值**/
    protected function _GetItem($id = null)
    {
        if ($id) {
            return EnumberateItem::find($id)->name;
        }
    }
    /**查询是否有期初**/

    protected function _checkInit($pid)
    {
        $list = AReceivable::where(['pid' => $pid, 'is_init' => 1])->get();

        if (count($list))
            return false;
        return true;
    }

    /**标识**/
    protected function _rowKey($len = 15)
    {
        $keys = "1qazxsw23edcvfr45tgbnhy67ujmki89olp0";
        $string = "";

        for ($i = 0; $i < $len; ++$i) {
            $index = mt_rand(0, strlen($keys) - 1);
            $string .= $keys[$index];
        }

        return $string;
    }

    /**用户名**/
    protected function _userName($id)
    {
        if ($id) {
            return User::find($id)->name;
        }
    }

    protected function _department($user_id)
    {
        if ($user_id) {
            $user = User::find($user_id);

            if ($user->department_id) {
                return Department::find($user->department_id)->name;
            }

        }
    }





    /**获取本周收款计划**/
    private function _GetPlan($pid)
    {
        $plan = ReceivablePlan::select('content','date','week')->where(['pid' => $pid])->orderBy('created_at', 'desc')->first();

        if ($plan) {
            $plan->date = date('Y年m月d日',strtotime($plan->date));
        }

        return $plan;
    }



}
