<?php

namespace App\Http\Controllers;

use Log;
use App\Refund;
use App\AReceivebill;
use App\ArrearsData;
use App\AReceivable;
use App\FilterProgram;
use App\InitialAmount;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class ARSumController extends Controller
{
    /**
     *  decimal save numbers
     */
    const DECIMALS = 3;
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
    protected $initial;

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
        Refund $refund,
        InitialAmount $initialAmount
    )
    {
        $this->arrearData = $arrearsData;
        $this->filter = $filter;
        $this->receivable = $receivable;
        $this->receivebill = $receivebill;
        $this->refund = $refund;
        $this->initial = $initialAmount;

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

        $lastItem = [];
        $result = [];
        $model = $this->arrearData->where($where)->whereIn("user_id",$this->UserAuthorizeIds());
        $rows = $model->orderBy("customer_name")->get();
        $total = $model->count();
        $rows = $this->makeMount($rows, $request->sortval ?? 0);

        foreach ($rows as $k => $v) {
            if ($k >= $request->offset && $k <= $request->limit + $request->offset - 1) {
                if (isset($lastItem["customer_name"])
                    && $lastItem["customer_name"] == $v["customer_name"]) {
                    $v["nameshow"] = false;
                } else {
                    $v["nameshow"] = true;
                    $lastItem = $v;
                }

                $v["rowkey"] = $this->rowKey();

                array_push($result, $v);
            }
        }

        return response(["total" => $total,"data" => $result], 200);
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

        //initials
        $initials = $this->InitialAmount($ids);

        //sales
        $s = $this->getDatas($ids, "receivable");

        //moneybacks
        $m = $this->getDatas($ids, "receivebill");

        //refunds
        $r = $this->getDatas($ids, "refund");

        /**
         * sales=> [
         *    0 =>  [
         *          "rid" => xx,
         *          "total" => xxx
         *          "amountf" => [1 => xxx,2=>xxx]
         *      ]
         *    ]
         */

        foreach ($ids as $k => $v) {

            $initial_total = 0;
            $start_month = 1;
            $sale_mount = $this->month_i;
            $back_amount = $this->month_i;
            $refund_mount = $this->month_i;
            $item = array(); //
            $sales = array();
            $refunds = array();
            $backs = array();

            foreach($initials as $iv) {
                if ($iv["rid"] == $v) {
                    //has initial
                    $initial_total = $iv["amount"];
                    $date = $iv["start"];

                    if (date("Y",$date) >= $this->year) {
                        $start_month = (int) date("m",$date);
                    }
                }
            }

            foreach ($s as $sv) {
                if ($sv["rid"] != $v)
                    continue;
                $sales = $sv;
            }

            foreach ($m as $mv) {
                if ($mv["rid"] != $v)
                    continue;
                $backs = $mv;
            }

            foreach ($r as $rv) {
                if ($rv["rid"] != $v)
                    continue;
                $refunds = $rv;
            }

            $monthly = [];
            $last_balance = $initial_total;
            for($i = 1; $i <=12; ++$i) {
                $sale_total = 0;
                $back_total = 0;
                $refund_total = 0;
                $balance_total = 0;
                $initial_total = 0;

                if ($i >= $start_month && $i <= $this->month) {

                    if (isset($sales["amount"]))
                        $sale_total = $sales["amount"][$i];

                    if (isset($refunds["amount"]))
                        $refund_total = $refunds["amount"][$i];

                    if (isset($backs["amount"]))
                        $back_total = $backs["amount"][$i];

                    $p1 = bcadd($sale_total,$initial_total,self::DECIMALS);
                    $p2 = bcadd($refund_total, $balance_total);
                    $p3 = bcsub($p1,$p2,self::DECIMALS);

                    $last_balance = $balance_total = $p3;

                }

                $monthly[$i]["initial"] = $initial_total;
                $monthly[$i]["sales"] = $sale_total;
                $monthly[$i]["refunds"] = $refund_total;
                $monthly[$i]["backs"] = $back_total;
                $monthly[$i]["balances"] = $balance_total;
            }

            foreach ($rows as &$row) {
                if ($row["id"] == $v) {
                    $row["monthly"] = $monthly;
                    //$item["coop_amount"] = $coopAmount;
                    $client_sales = isset($sales["client"]) ? $sales["client"] : 0;
                    $client_refunds = isset($refunds["client"]) ? $refunds["client"] : 0;
                    $collegaue_sales = isset($sales["colleague"]) ? $sales["colleague"] : 0;
                    $collegaue_refunds = isset($refunds["colleague"]) ? $refunds["colleague"] : 0;

                    $row["client_total"] = bcsub($client_sales, $client_refunds,self::DECIMALS);
                    $row["colleague_total"] = bcsub($collegaue_sales, $collegaue_refunds,self::DECIMALS);
                }
            }

        }

        $result = $rows->toArray();

        if ($sort == 1) {
            //order by arrears
           // return $rows->sortByDesc(function($item, $key) {return $item["arrears"];})->toArray();
            //$result = $this->quickSortDesc($result);
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
    private function getDatas(array $ids,string $modelName) : array
    {
        $result = [];
        $rows = $this->$modelName->whereIn("rid",$ids)->get();

        if ($rows) {
            $rows = collect($rows)->groupBy("rid")->toArray();
        }
        else {
            return $result;
        }

        $result = $this->sumAmount($rows);

        return $result;
    }


    /**
     *  init amount
     */
    private function InitialAmount(array $ids) :array
    {
        $result = [];
        $rows = $this->initial->whereIn("rid",$ids)->get();

        if ($rows) {
            $rows = $rows->groupBy("rid")->toArray();
        }
        else {
            return $result;
        }

        foreach($rows as $k => $v) {
            $amount = 0;
            $row = collect($v);
            $date_t = $row->pluck("date")->toArray();
            asort($date_t);
            $start = $date_t[0]; //begin date

            array_walk($v, function($item) use (&$amount) {
                $amount = bcadd($amount, $item["amountfor"],self::DECIMALS);
            });

            array_push($result, ["rid" => $k,"start" => $start,"amount" => $amount]);
        }

        return $result;
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
            $t = ["total" => 0, "rid" => $k,"amount" => $this->month_i,"client" => 0,"colleague" => 0];

            foreach ($v as $vv) {
                if ($vv["date"] <= $this->year_t) {
                    $t["total"] = bcadd($t["total"], $vv["amountfor"], self::DECIMALS);
                }
                else {
                    $i = (int) date("m",$vv["date"]);
                    $t["amount"][$i] = bcadd($t["amount"][$i], $vv["amountfor"], self::DECIMALS);
                }

                if (isset($vv["type"])) {
                    if ($vv["type"] == 0) {
                        $t["client"] = bcadd($t["client"], $vv["amountfor"],self::DECIMALS);
                    }
                    else {
                        $t["colleague"] = bcadd($t["colleague"], $vv["amountfor"],self::DECIMALS);
                    }
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

        for ($i = 1; $i < count($arr); ++$i) {
            if ($arr[$i]["arrears"] > $base["arrears"])
                array_push($left_arr,$arr[$i]);
            else
                array_push($right_arr, $arr[$i]);
        }

        $left_arr = $this->quickSortDesc($left_arr);
        $right_arr = $this->quickSortDesc($right_arr);

        return array_merge($left_arr,[$base] ,$right_arr);
    }
}
