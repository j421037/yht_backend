<?php
/**
* 收款单处理类
* @author 2018-09-19
*/
namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ARSumRole;
use Auth;
use Event;
use App\AReceivebill;
use App\Events\ARLogEvent;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Requests\ReceivebillRequest;
use App\Http\Requests\ReceivebillUpdateRequest;
use App\Http\Resources\ReceivebillListResource;


class ReceivebillController extends Controller
{
    use ARSumRole;

    public function store(ReceivebillRequest $request)
    {
    	$list = $request->all();

    	$list['date'] = strtotime($list['date']);

    	try {
    		if ($result = AReceivebill::create($list)) {

                Event::fire(new ARLogEvent($this->getUserId(), $result->id, 'create', 'AReceivebill', $result->amountfor, $result->amountfor));

    			return response(['status' => 'success'], 200);
    		}
    	} catch (QueryException $e) {
    		return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
    	}
    }

    public function update(ReceivebillUpdateRequest $request)
    {
    	$Receivebill = AReceivebill::find($request->id);

        $oldValue = $Receivebill->amountfor;
    	$Receivebill->amountfor = trim($request->amountfor);
    	$Receivebill->date = strtotime($request->date);
        $Receivebill->discount = trim($request->discount);
        $Receivebill->remark = trim($request->remark);

    	try {
    		if ($Receivebill->save()) {

                Event::fire(new ARLogEvent(
                            $this->getUserId(),
                            $Receivebill->id, 
                            'update', 
                            'AReceivebill', 
                            $oldValue, 
                            $Receivebill->amountfor
                        )
                );

    			return response(['status' => 'success'], 200);
    		} 
    	} catch(QueryException $e) {
    		return response(['status' => 'err', 'errmsg' => $e->getMessage()]);
    	}
    }
    /**
    * 返回数据
    */
    public function all(Request $request)
    {
        $limit = $request->limit ?? 5;
        $offset = $request->offset ?? 0;

        try {

            $list = AReceivebill::where(['rid' => $request->rid])->limit($limit)->offset($offset)->orderBy('date', 'desc')->get();
            $total = AReceivebill::where(['rid' => $request->rid])->count();

            return response(['row' => ReceivebillListResource::collection($list),'total' => $total], 200);
        } 
        catch(QueryException $e) {
            return response(['status' => 'err', 'errmsg' => $e->getMessage()]);
        }
    }

    public function del(Request $request)
    {
        try {
            if (AReceivebill::destroy($request->id)) {
                return response(['status' => 'success']);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}
