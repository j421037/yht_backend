<?php
/**
* 应收单处理类
* @author 2018-09-19
*/
namespace App\Http\Controllers;

use Auth;
use Event;
use App\AReceivable;
use App\InitialAmount;
use App\Events\ARLogEvent;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Requests\ReceivableEntryRequest;
use App\Http\Requests\ReceUpdateRequest;
use App\Http\Resources\ReceivableListResource;
use App\Http\Controllers\Traits\ARSumRole;
use App\Exceptions\UserAuthorizationException;

class ReceivableController extends Controller
{
    use ARSumRole;

    public function all(Request $request)
    {
        $limit = $request->limit ?? 5;
        $offset = $request->offset ?? 0;

        try {

            $list = AReceivable::where(['rid' => $request->rid])->limit($limit)->offset($offset)->orderBy('date', 'desc')->get();

            $total = AReceivable::where(['rid' => $request->rid])->count();

            return response(['row' => ReceivableListResource::collection($list), 'total' => $total], 200);
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }

    public function store(ReceivableEntryRequest $request)
    {
    	$list = [];
    	$list["rid"] = $request->rid;
    	$list["amountfor"] = $request->amountfor;
    	$list["remark"] = $request->remark;
    	$list['date'] = strtotime($request->date);
    	$row = InitialAmount::where(["rid" => $request->rid])->orderBy("date","asc")->first();

    	if ($row && $list["date"] < $row->date) {
    	    return response(["status" => "error","errmsg" => "销售记录不能在期初: ".date("Y年m月d日",$row->date)."之前"],200);
        }

    	try {
    		if ($result = AReceivable::create($list)) {

                Event::fire(new ARLogEvent($this->getUserId(), $result->id, 'create', 'AReceivable', $result->amountfor, $result->amountfor));

    			return response(['status' => 'success'], 200);
    		}
    	} catch(QueryException $e) {
    		return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
    	} 
    }
    public function update(ReceUpdateRequest $request)
    {
    	
    	$Receivable = AReceivable::find($request->id);
        $oldValue = $Receivable->amountfor;
    	$Receivable->amountfor = trim($request->amountfor);
    	$Receivable->date = strtotime($request->date);
        $Receivable->remark = trim($request->remark);

    	try {
    		if ($Receivable->save()) {

                Event::fire(new ARLogEvent(
                            Auth::user()->id, 
                            $Receivable->id, 
                            'update', 
                            'AReceivable', 
                            $oldValue, 
                            $Receivable->amountfor
                        )
                );

    			return response(['status' => 'success'], 200);
    		} 
    	} catch(QueryException $e) {
    		return response(['status' => 'err', 'errmsg' => $e->getMessage()]);
    	}
    }

    public function delete(Request $request)
    {
        try {

            if (AReceivable::destroy($request->id)) {
                return response(['status' => 'success']);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }

    }
}
