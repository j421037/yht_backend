<?php
/**
* 首页处理类
* @author 
*/
namespace App\Http\Controllers;

use App\ArrearsData;
use Auth;
use Event;
use App\Project;
use App\IndexStatistics;
use App\AReceivable;
use App\AReceivebill;
use App\Events\ARLogEvent;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Resources\IndexResource;
use App\Exceptions\UserAuthorizationException;

class IndexController extends Controller
{
    protected $indexStatis;
	public function __construct(IndexStatistics $index)
    {
        $this->indexStatis = $index;
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

			$list = IndexStatistics::whereIN('user_id', array_keys($this->UserAuthorizeCollects()))->get();
			
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
	
	//个人销售额
    public function getSales(Request $request)
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
