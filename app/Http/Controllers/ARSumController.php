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
        $lastItem = [];
        $rows = $this->makeMount($rows, $request->sortval ?? 0);

        foreach ($rows as &$v) {
            if (isset($lastItem["customer_name"])
                && $lastItem["customer_name"] == $v["customer_name"]) {
                $v["nameshow"] = false;
            }
            else {
                $v["nameshow"] = true;
                $lastItem = $v;
            }

            $v["rowkey"] = $this->rowKey();
        }

        return response(["total" => $total,"data" => $rows], 200);
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

            $lastArr = 0;
            $initial = [];
            $initMonth = 0;
            $t[1]["begin"] = bcsub(bcsub($sbegin,$mbegin, self::DECIMALS), $rbegin, self::DECIMALS);
            $coopAmount =  bcsub($sbegin, $rbegin, self::DECIMALS);

            if (isset($s["initial"])) {
                $initial = $s["initial"];
                $initMonth = date("m", $initial["date"]);
            }

            foreach($t as $month => $tv) {

                if ($month <= $this->month) {

                    if (!isset($t[$month]["begin"])) {
                        $t[$month]["begin"] = $lastArr;
                    }

                    if ((int)$initMonth == (int)$month){
                        $t[$month]["begin"] = $initial["amountfor"];
                        $coopAmount = bcadd($coopAmount, $initial["amountfor"], self::DECIMALS);
                    }
                }
                else {
                    $t[$month]["begin"] = 0;
                }

                $t[$month]["sales"] = $smount[$month];
                $t[$month]["refunds"] = $ramount[$month];
                $t[$month]["money_back"] = $mamount[$month];

                $p0 = bcadd($smount[$month],$t[$month]["begin"],self::DECIMALS);
                $p1 = bcadd($t[$month]["refunds"],$t[$month]["money_back"], self::DECIMALS);

                $t[$month]["arrears"] = $lastArr = bcsub($p0, $p1,self::DECIMALS);
                $coopAmount = bcsub(bcadd($coopAmount, $t[$month]["sales"], self::DECIMALS),$t[$month]["refunds"],self::DECIMALS);

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
                    $t["beginning"] = bcadd($t["beginning"],$item["amountfor"],self::DECIMALS);
                }
                else if ($item["is_init"] != 1){
                    $i = date("m",$item["date"]);

                    $amount[(int)$i] = bcadd($amount[(int)$i], $item["amountfor"], self::DECIMALS);
                }

                if ($item["is_init"] == 1 && count($r) < 1) {
                    $r = $item;
                }

            });

            if ($t["beginning"] <= 0 && $r) {
                $t["initial"] = $r;
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
                    $t["beginning"] = bcadd($t["beginning"], $vv["amountfor"], self::DECIMALS);
                }
                else {
                    $i = (int) date("m",$vv["date"]);
                    $t["amount"][$i] = bcadd($t["amount"][$i], $vv["amountfor"], self::DECIMALS);
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
