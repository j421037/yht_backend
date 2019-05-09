<?php

namespace App\Http\Controllers;

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

        for($i = 1; $i <= $this->month; ++ $i) {
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

        return response(["total" => $total,"data" => $rows,"result" => $this->sales($rows->pluck("id")->toArray())], 200);
    }

    /**
     * set where condition
     */
    private function where(array $condition):array
    {
        $arr = [];

        foreach ($condition as $v) {
            array_push($arr,[$v["field"],$this->operatorMap[$v["operator"]], $v["value"] ]);
        }

        return $arr;
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
                else {
                    $i = date("m",$item["date"]);

                    $amount[(int)$i] = bcadd($amount[(int)$i], $item["amountfor"], 3);
                }

                if ($item["is_init"] == 1 && count($r) < 1) {
                    $r = $item;
                }

            });

            //last year has data
            if ($t["beginning"] > 0) {
                $amount[1] += bcadd($t["beginning"], $amount[1], 3);
            }
            else {
                if ($r) {
                    $t["beginning"] = $r["amountfor"];
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
        $rows = $this->refund->whereIn("pid",$ids)->get();
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
}
