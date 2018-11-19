<?php

namespace App\Http\Resources;

use App\User;
use App\ARType;
use App\AReceivebill;
use App\AReceivable;
use App\Refund;
use App\ReceivablePlan;
use App\EnumberateItem;
use App\Http\Resources\ARDetailResource;
use App\Http\Resources\RefoundResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ARSumResource extends JsonResource
{
    const MonthMap = [
        "一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"
    ];
    
    private $_receivable;
    private $_receivebill;
    private $_continueReceivable;//持续欠款月数
    // private $_currentDate;//当前年

    // public function __construct($resource,$date)
    // {
    //     parent::__construct($resource);
    //     $this->_currentDate = $date;
    // }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) 
    {
        return [
            'status'        => $this->_GetItem($this->status),
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
            'has_init'       => $this->_checkInit($this->pid),           
        ];
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

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray1($request)
    {

        return [
            'id'                => $this->id,
            'pid'               => $this->pid,
            'user_id'           => $this->user_id,
            'cust_id'           => $this->cust_id,
            'user_name'         => $this->user_name,
            'name'              => $this->name,
            'project'           => $this->project,
            'amountfor'         => number_format($this->amountfor, 2),
            'real_amountfor'    => number_format($this->real_amountfor, 2),
            'discount'          => number_format($this->discount, 2),
            'balance'           => number_format($this->balance, 2),
            'receivable'        => $this->_buildData($this->pid, new AReceivable, $this->_receivable),
            'receivebill'       => $this->_buildData($this->pid, new AReceivebill, $this->_receivebill),
            'ar_sum'            => $this->_buildMonthSum($this->pid),
            'refund_sum'        => $this->refund,
            'refund'            => $this->_refund($this->pid),
            'end_init'          => $this->_isInit($this->cust_id),
            'agreement'         => $this->agreement,
            'type'              => $this->type,
            'tax'               => $this->tax,
            'payment_days'      => $this->payment_days,
            'continueReceivable'=> $this->_ReceMonth(),
            'plan'              => $this->_GetPlan($this->pid),
        ];
    }
    
    /**
    * 构建应收收款数据
    * @param &$vars 引用传递
    */
    private function _buildData($pid, $model, &$vars)
    {
        $list = $model::where(['pid' => $pid])->orderBy('date', 'desc')->get();
        $vars = $model::with(['type'])
                        ->where(['pid' => $pid])
                        ->selectRaw("*, from_unixtime(date,'%Y') as `year`,from_unixtime(date,'%m') as `month` ")
                        ->orderBy('year','month')
                        ->get();

        return ARDetailResource::collection($list);
    }

    /**构建退款**/
    private function _refund($pid)
    {
        $list = Refund::where(['pid' => $pid])->orderBy('date', 'desc')->get();

        return RefoundResource::collection($list);
    }

    private function _ReceMonth()
    {
        return $this->_continueReceivable;
    }

    private function _buildMonthSum($pid)
    {
        $item = new \StdClass;
        $this->_continueReceivable = 0;
        // $ARType = ARType::all(); //业务类型
        $m1 = $this->_receivebill->pluck('year')->toArray();
        $m2 = $this->_receivable->pluck('year')->toArray();
        $m3 =  array_unique(array_merge($m1, $m2));
        sort($m3);

        $lastYearEndAmount = 0; //上一年的期末
        $currentYear = date('Y', time());
        $currentMonth = date('m', time());

        foreach ($m3 as $v) {
            $m = [];

            foreach (self::MonthMap as $mk => $mv) {
                $month = new \StdClass;
                $month->name = $mv;
                $month->init_amountfor = 0;//本月期初应收
                $month->amountfor = 0; //销售应收
                $month->real_amountfor = 0; //收款
                $month->end_amountfor = 0;//期末
                array_push($m, $month);
            } 

            //应收
            foreach ($this->_receivable as $rk => $rv) {
                $index = intVal($rv->month) - 1;

                if ($rv->year == $v) {

                    if ($rv->is_init == 0) {
                        $m[$index]->amountfor = bcadd($m[$index]->amountfor, $rv->amountfor, 3);
                    } else {
                        $m[$index]->init_amountfor = bcadd($m[$index]->init_amountfor, $rv->amountfor, 3); 
                    }
                }
            }

            //收款
            foreach ($this->_receivebill as $lk => $lv) {
                $index = intVal($lv->month) - 1;

                if ($lv->year == $v) {
                     
                    $m[$index]->real_amountfor = bcadd($m[$index]->real_amountfor, $lv->amountfor, 3);

                }
            }

            //开始计算
            $lastMonthEndAmount = 0; //上月期末
            

            foreach ($m as $mk => $mv) {

                //跨年期初 一月期初
                if ($mk == 0) {
                    $mv->init_amountfor = bcadd($mv->init_amountfor, $lastYearEndAmount, 3);
                }
                
                //如果当前下个自然月的时间戳大于历史的时间戳 则可以输出数据
                if (mktime(0,0,0,$mk+1,1,$v) < mktime(0,0,0,$currentMonth+1, 1, $currentYear)) {

                    $mv->init_amountfor = bcadd($mv->init_amountfor, $lastMonthEndAmount, 3);//本月期初  = 上月余额

                    $mv->end_amountfor = bcsub(bcadd($mv->init_amountfor, $mv->amountfor, 3), $mv->real_amountfor, 3);//期末欠款

                    $lastMonthEndAmount = $mv->end_amountfor;//结存到下个月的期初

                    //12月 年末余额
                    if ($mk == count($m) - 1) {
                        $lastYearEndAmount = $mv->end_amountfor;
                    }
                
                    //连续欠款月数
                    if ($mv->real_amountfor > 0) {

                        $this->_continueReceivable = 0;

                    } else {

                        $this->_continueReceivable +=1;
                    }
                }
            } 

            $item->$v = $m;
        }

        
        return $item;
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

    /**查询是否有期初**/
    private function _isInit($cusid)
    {
        $list = AReceivable::where(['cust_id' => $cusid, 'is_init' => 1])->first();

        return (bool)$list;
    }

}
