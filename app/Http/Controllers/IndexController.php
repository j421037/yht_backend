<?php
/**
* 首页处理类
* @author 
*/
namespace App\Http\Controllers;

use Log;
use App\Project;
use App\IndexStatistics;
use App\AReceivable;
use App\AReceivebill;
use App\ArrearsData;
use App\InitialAmount;
use App\Refund;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Resources\IndexResource;

class IndexController extends Controller
{
    const DECIMALS = 3;
    protected $indexStatis;
    protected $arrearData;
    protected $arble;
    protected $arbill;
    protected $refund;
    protected $initial;
    protected $year_t;
    protected $month;
    protected $monthly = [];

	public function __construct(
	    IndexStatistics $index,
        ArrearsData $arrearsData,
        AReceivable $arble,
        AReceivebill $arbill,
        InitialAmount $initialAmount,
        Refund $refund
    )
    {
        $this->indexStatis = $index;
        $this->arble = $arble;
        $this->arbill = $arbill;
        $this->arrearData = $arrearsData;
        $this->refund = $refund;
        $this->initial = $initialAmount;

        for ($i = 1; $i <= 12; ++ $i) {
            $this->monthly[$i] = 0;
        }

        $this->year_t = strtotime(date("Y",time())."-01"."-01");
        $this->month = (int) date("m",time());
    }

    //基本信息
	public function getData()
	{
		try {
            $row = $this->indexStatis->where(["user_id" => $this->getUserId()])->first();

		    if (!$row)
            {
                //创建一条自己的记录
                $model = $this->indexStatis->newInstance();
                $model->user_id = $this->getUserId();
                $model->save();
            }

			$list = $this->indexStatis->whereIN('user_id', array_keys($this->UserAuthorizeCollects()))->get();
			
			$target = 0;
			$completed = 0;
			$debt = 0;
			$debt_percent = 0;
			$target_client = 0;
			$report_client = 0;
			$coop_client = 0;
			$lose_client = 0;
			$brand_price = 0;
			$rt_price = 0;
			$other_price = 0;
			$machine = 0;
			$censor = 0;
			$mynote = 0;
			$likes = 0;
			
			foreach($list as $value) {
				$target += $value['target'];
				$completed += $value['completed'];
				$debt += $value['debt'];
				$debt_percent +=  $value['debt_percent'];
				$target_client += $value['target_client'];
				$report_client += $value['report_client'];
				$coop_client += $value['coop_client'];
				$lose_client += $value['lose_client'];
				$brand_price += $value['brand_price'];
				$rt_price += $value['rt_price'];
				$other_price += $value['other_price'];
				$machine += $value['machine'];
				$censor += $value['censor'];
				$mynote += $value['mynote'];
				$likes += $value['likes'];
			}

			$data = ['target'=>$target, 'completed'=>$completed, 'debt'=>$debt,'debt_percent'=> $debt_percent,'target_client'=>$target_client,'report_client'=>$report_client,
			'coop_client'=>$coop_client,'lose_client'=>$lose_client,'brand_price'=>$brand_price,'rt_price'=>$rt_price,'other_price'=>$other_price,'machine'=>$machine,'censor'=>$censor,'mynote'=>$mynote,'likes'=>$likes];
			
            return response(['row' => json_encode($data)], 200);
        }
        catch (QueryException $e) { 
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
	}

	public function getSales(Request $request)
    {
        $rows = $this->arrearData->where(["user_id" => $request->user_id])->get();
        $ids = [];

        if ($rows)
            $ids = $rows->pluck("id")->toArray();

        if (count($ids) < 1) {
            return response(["data" => $this->monthly], 200);
        }

        $initials = $this->initial->whereIn("rid",$ids)->get();
        $sales = $this->arble->whereIn("rid", $ids)->get();
        $backs = $this->arbill->whereIn("rid", $ids)->get();
        $refunds = $this->refund->whereIn("rid", $ids)->get();

        $history_initial = 0;
        $history_sales = 0;
        $history_backs = 0;
        $history_refunds = 0;
        $history_total = 0;
        $totals = $this->monthly;

        //期初 累加
        foreach ($initials as $v) {
            $history_initial = bcadd($history_initial, $v["amountfor"], self::DECIMALS);
        }

        foreach ($sales as $k => $v) {
            if ($v["date"] < $this->year_t) {
                $history_sales = bcadd($history_sales, $v["amountfor"], self::DECIMALS);
                //unset($sales[$k]);
            }
        }

        foreach ($backs as $k => $v) {
            if ($v["date"] < $this->year_t) {
                $history_backs = bcadd($history_backs, $v["amountfor"], self::DECIMALS);
               // unset($backs[$k]);
            }
        }

        foreach ($refunds as $k => $v) {
            if ($v["date"] < $this->year_t) {
                $history_refunds = bcadd($history_refunds, $v["amountfor"], self::DECIMALS);
               // unset($refunds[$k]);
            }
        }

        $history_total = bcsub(bcadd($history_initial, $history_sales,self::DECIMALS),bcadd($history_backs,$history_refunds,self::DECIMALS), self::DECIMALS);

        foreach ($totals as $k => &$v) {
            $sale_total = 0;
            $back_total = 0;
            $refund_total = 0;
            $debt_total = 0;
            $v = [];

            if ($this->month >= $k ) {
                $v["initial"] = $history_total;

                foreach ($sales as $sv) {
                    if ($sv["date"] > $this->year_t) {
                        if ((int)date("m", $sv["date"]) == $k)
                            $sale_total = bcadd($sale_total, $sv["amountfor"], self::DECIMALS);
                    }
                }

                foreach ($backs as $bv) {
                    if ($bv["date"] > $this->year_t) {
                        if ((int)date("m", $bv["date"]) == $k)
                            $back_total = bcadd($back_total, $bv["amountfor"], self::DECIMALS);
                    }
                }

                foreach ($refunds as $rv) {
                    if ($rv["date"] > $this->year_t) {
                        if ((int) date("m", $rv["date"]) == $k)
                            $refund_total = bcadd($refund_total, $rv["amountfor"], self::DECIMALS);
                    }
                }

                $tmp1 = bcadd($history_total, $sale_total,self::DECIMALS);
                $tmp2 = bcadd($back_total, $refund_total, self::DECIMALS);
                $history_total = $debt_total = bcsub($tmp1,$tmp2,self::DECIMALS);
            }


            $v["sales"] = $sale_total;
            $v["received"] = $back_total;
            $v["refunds"] = $refund_total;
            $v["debt"] = $debt_total;
        }

        return response(["data" => $totals], 200);
    }
    public function users()
    {
        $result = [];
        $users = $this->UserAuthorizeCollects();

        foreach ($users as $k => $user) {
            array_push($result,["label" => $user,"value" => $k]);
        }

        return response(["data" => $result], 200);
    }
	//个人销售额
    public function getSales1(Request $request)
    {
		try {
			$data = array();
            $p = ArrearsData::where(['user_id' => $request->user_id])->get()->pluck("id");
            $res = AReceivable::whereIN('rid', $p)->get();
            $year = date("Y", time());

			for ($i=1; $i <= 12; $i++) {

				if($res) {
					$total = 0;
					foreach($res as $value) {
					    $start = mktime(0,0,0,$i,1,$year);
					    $end = mktime(0,0,0,$i + 1,1,$year);
					    if ($value["date"] >= $start && $value["date"] < $end) {
                            $total += $value['amountfor'];
                        }
					}
					$data[] = $total;
				} else {
					$data[] = 0;
				}
			}
			return response(['data' => $data], 200);
		}
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
	
	//个人回款
	public function getReceived(Request $request)
    {
		try {
			$data = array();
			for ($i=1; $i<=12; $i++) {
				$p = Project::where(['created_year' => $request->created_year, 'created_month' => $i, 'user_id' => $request->user_id])->get();
				if($p) {
					$res = AReceivebill::whereIN('pid', $p)->get();
					$total = 0;
					foreach($res as $value) {
						$total += $value['amountfor'];
					}
					$data[] = $total;
				} else {
					$data[] = 0;
				}
			}
			return response(['data' => json_encode($data)], 200);
		}
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
	
	//累计欠款
	public function getDebt(Request $request)
    {
		try {
			$data = array();
			for ($i=1; $i<=12; $i++) {
				$p = Project::where(['created_year'=>$request->created_year, 'created_month' => $i, 'user_id' => $request->user_id])->get();
				if($p) {
					$re = AReceivable::whereIN('pid', $p)->get();
					$total = 0;
					foreach($re as $value) {
						$total += $value['amountfor'];
					}
					
					$total2 = 0;
					$rb = AReceivebill::whereIN('pid', $p)->get();
					foreach($rb as $value) {
						$total2 += $value['amountfor'];
					}
					
					$data[] = $total - $total2;
					
				} else {
					$data[] = 0;
				}
			}
			return response(['data' => json_encode($data)], 200);
		}
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}
