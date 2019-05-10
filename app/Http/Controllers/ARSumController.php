<?php

namespace App\Http\Controllers;

use Log;
use App\AReceivebill;
use App\ArrearsData;
use App\AReceivable;
use App\FilterProgram;
use App\Refund;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class ARSumController extends Controller
{
    /**
     * arrearData model
     */
    protected $arrearData;

    /**
     * FilterProgam model
     */
    protected $filter;

    /**
     * AReceivable model
     */
    protected $receivable;
    protected $receivebill;
    protected $refund;

    /**
     *  date
     */
    protected $year;
    protected $year_t;
    protected $month;
    protected $month_i = [];
    protected $month_section;

    public function __construct(
        ArrearsData $arrearsData,
        FilterProgram $filter,
        AReceivable $receivable,
        AReceivebill $receivebill,
        Refund $refund
    )
    {
        $this->arrearData = $arrearsData;
        $this->filter = $filter;
        $this->receivable = $receivable;
        $this->receivebill = $receivebill;
        $this->refund = $refund;

        $this->year = date("Y",time());
        $this->month = date("m", time());
        $this->year_t = strtotime($this->year."-01"."-01");

        for($i = 1; $i <= 12; ++ $i) {
            $this->month_i[$i] = 0;
        }
    }

    /**
     * query arrears
     */
    public function query(Request $request)
    {
        $filter = $request->conf;

        if ($request->initialization == true) {

            $filter = $this->filter->where(["default" => 1, "user_id" => $this->getUserId()])->first();

            if (isset($filter->conf))
                $filter = json_decode($filter->conf, true);
        }

        if ($filter) {
            $where = $this->where($filter);
        }
        else {
            $where = [];
        }

        $model = $this->arrearData->where($where)->whereIn("user_id",$this->UserAuthorizeIds());
        $rows = $model->orderBy("customer_name")->get();
        $total = $model->count();

        $lastItem = new \StdClass;

        $rows->map(function(&$item) use (&$lastItem) {

            if (isset($lastItem->customer_name)
                && $lastItem->customer_name == $item->customer_name) {
                $item->nameshow = false;
            }
            else {
                $item->nameshow = true;
                $lastItem = $item;
            }

            $item->rowkey = $this->rowKey();
        });


        return response(["total" => $total,"data" => $this->makeMount($rows, $request->sortval ?? 0)], 200);
    }

    /**
     * set where condition
     */
    private function where(array $condition):array
    {
        $arr = [];

        foreach ($condition as $v) {
            if ($v["value"] != null || $v["value"] != "")
                array_push($arr,[$v["field"],$this->operatorMap[$v["operator"]], $v["value"] ]);
        }

        return $arr;
    }

    /**
     * calculate amount
     */
    private function makeMount(Collection $rows, int $sort = 0):array
    {

        //ids
        $ids = $rows->pluck("id")->toArray();

        //sales
        $sales = $this->sales($ids);

        //moneybacks
        $moneyBacks = $this->MoneyBack($ids);

        //refunds
        $refunds = $this->refunds($ids);

        /**
         * sales=> [
         *    0 =>  [
         *          "rid" => xx,
         *          "beginning" => xxx
         *          "amountf" => [1 => xxx,2=>xxx]
         *      ]
         *    ]
         */

        foreach ($ids as $k => $v) {
            $s = [];
            $m = [];
            $r = [];
            $t = [];
            $tmp = ["rid" => $v];
            $mbegin = 0;
            $rbegin = 0;
            $sbegin = 0;
            $smount = $this->month_i;
            $mamount = $this->month_i;
            $ramount = $this->month_i;

            foreach ($moneyBacks as $mv) {
                if ($v != $mv["rid"])
                    continue;
                $m = $mv;
            }

            foreach ($refunds as $rv) {
                if ($v != $rv["rid"])
                    continue;
                $r = $rv;
            }

            foreach ($sales as $sv) {
                if ($v != $sv["rid"])
                    continue;
                $s = $sv;
            }

            if (isset($s["beginning"])) {
                $sbegin = $s["beginning"];
            }

            if (isset($m["beginning"]))
            {
                $mbegin = $m["beginning"];
            }

            if (isset($r["beginning"])) {
                $rbegin = $r["beginning"];
            }

            if (isset($s["amount"])) {
                $smount = $s["amount"];
            }

            if (isset($r["amount"])) {
                $ramount = $r["amount"];
            }

            if (isset($m["amount"])) {
                $mamount = $m["amount"];
            }

            for ($i = 1; $i <= $this->month;++$i) {
                $t[$i] = [];
            }

            $t[1]["begin"] = bcsub(bcsub($sbegin,$mbegin, 3), $rbegin, 3);
            $coopAmount =  bcsub($sbegin, $rbegin, 3);
            $lastArr = 0;

            foreach($t as $month => $tv) {

                if ($month <= $this->month) {

                    if (!isset($t[$month]["begin"])) {
                        $t[$month]["begin"] = $lastArr;
                    }

                }
                else {
                    $t[$month]["begin"] = 0;
                }

                $t[$month]["sales"] = $smount[$month];
                $t[$month]["refunds"] = $ramount[$month];
                $t[$month]["money_back"] = $mamount[$month];

                $p0 = bcadd($smount[$month],$t[$month]["begin"],3);
                $p1 = bcadd($t[$month]["refunds"],$t[$month]["money_back"], 3);

                $t[$month]["arrears"] = $lastArr = bcsub($p0, $p1,3);
                $coopAmount = bcsub(bcadd($coopAmount, $t[$month]["sales"], 3),$t[$month]["refunds"],3);

            }

            $tmp["info"] = $t;

            $rows->map(function(&$item) use (&$tmp, &$coopAmount) {
                if ($item["id"] == $tmp["rid"]) {
                    $item["monthly_sales"] = $tmp["info"];
                    $item["coop_amount"] = $coopAmount;
                    $item["begin"] = $tmp["info"][1]["begin"];
                    $item["arrears"] = $tmp["info"][(int)$this->month]["arrears"];
                }
            });
        }

        $result = $rows->toArray();

        if ($sort == 1) {
            //order by arrears
           // return $rows->sortByDesc(function($item, $key) {return $item["arrears"];})->toArray();
            $result = $this->quickSortDesc($result);
        }



        return $result;
    }

    /**
     * get sum of sales
     *
     * @param  $id row id
     *
     * @return array
     */
    private function sales(array $ids) : array
    {
        $result = [];
        $rows = $this->receivable->whereIn("rid",$ids)->get();

        if ($rows) {
            $rows = collect($rows)->groupBy("rid")->toArray();
        }
        else {
            return $result;
        }

        foreach ($rows as $k => $v) {
            $amount = $this->month_i;
            $r = [];
            $t = ["beginning" => 0,"rid" => $k, "amount" => $this->month_i];

            array_walk($v,function($item) use (&$t,&$amount,&$r){

                // accumulate beginning
                if ($item["date"] <= $this->year_t) {
                    $t["beginning"] = bcadd($t["beginning"],$item["amountfor"],3);
                }
                else if ($item["is_init"] != 1){
                    $i = date("m",$item["date"]);

                    $amount[(int)$i] = bcadd($amount[(int)$i], $item["amountfor"], 3);
                }

                if ($item["is_init"] == 1 && count($r) < 1) {
                    $r = $item;
                }

            });

            //last year has data
            if ($t["beginning"] > 0) {
                //$amount[1] += bcadd($t["beginning"], $amount[1], 3);
            }
            else {
                if ($r) {
//                    $t["beginning"] = $r["amountfor"];
                }
            }

            $t["amount"] = $amount;

            array_push($result,$t);
        }
        return $result;
    }

    /**
     * refunds
     *
     * @param  $id row id
     *
     * @return array
     */
    private function refunds(array $ids):array
    {
        $rows = $this->refund->whereIn("rid",$ids)->get();

        if ($rows) {
            $rows = $rows->groupBy("rid")->toArray();
        }
        else {
            return [];
        }

        return $this->sumAmount($rows);
    }

    /**
     * money back
     *
     *  @param  $id row id
     *
     * @return array
     */
    private function MoneyBack(array $ids) :array
    {
        $rows = $this->receivebill->whereIn("rid",$ids)->get();

        if ($rows) {
            $rows = $rows->groupBy("rid")->toArray();
        }
        else {
            return [];
        }

        return $this->sumAmount($rows);
    }

    /**
     * [
     *    "6(rid)" => [
     *            [amount => 111],
     *            [amount => 22]...
     *      ]
     * ]
     * @param amount data
     * @return format amount array
     */
    private function sumAmount(array $rows) :array
    {
        $result = [];

        foreach ($rows as $k => $v) {
            $t = ["beginning" => 0, "rid" => $k,"amount" => $this->month_i];

            foreach ($v as $vv) {
                if ($vv["date"] <= $this->year_t) {
                    $t["beginning"] = bcadd($t["beginning"], $vv["amountfor"], 3);
                }
                else {
                    $i = (int) date("m",$vv["date"]);
                    $t["amount"][$i] = bcadd($t["amount"][$i], $vv["amountfor"], 3);
                }
            }

            //$t["amount"] = $amounts;
            array_push($result,$t);
        }

        //Log::info(json_encode($result));
        return $result;
    }

    /**
     * key
     * @return string
     */
    private function rowKey($len = 15):string
    {
        $keys = "1qazxsw23edcvfr45tgbnhy67ujmki89olp0";
        $string = "";

        for ($i = 0; $i < $len; ++$i) {
            $index = mt_rand(0, strlen($keys) - 1);
            $string .= $keys[$index];
        }

        return $string;
    }

    /**quick sort**/
    private function quickSortDesc(array $arr)
    {
        if (count($arr) <= 1)
            return $arr;

        $base = $arr[0];
        $left_arr = [];
        $right_arr = [];

        foreach ($arr as $v) {

            if ($v["arrears"] > $base["arrears"])
                array_push($left_arr,$v);
            else
                array_push($right_arr, $v);
        }

//        $left_arr = $this->quickSortDesc($left_arr);
//        $right_arr = $this->quickSortDesc($right_arr);
//
        return array_merge($left_arr ,$right_arr);
    }
}
