<?php
/**
* 退货单类
* @author 王鑫
* 2018-10-16
*/
namespace App\Http\Controllers;

use App\Refund;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Requests\RefundStoreRequest;
use App\Http\Requests\RefundUpdateRequest;

class RefundController extends Controller
{
    public function store(RefundStoreRequest $request)
    {
    	try {

    		$data = $request->all();
    		$data['date'] = strtotime($data['date']);

    		if (Refund::create($data)) {
    			return response(['status' => 'success'], 200);
    		}

    	} catch(QueryException $e) {

    		return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
    	}
    }

    public function update(RefundUpdateRequest $request)
    {
        $model = Refund::find($request->id);

        if ($model) {

            $model->date    = strtotime($request->date);
            $model->refund  = $request->refund;
            $model->remark  = $request->remark;

            try {

                if ($model->save()) {
                    return response(['status' => 'success']);
                } 

            } catch (QueryException $e) {

                return response(['status' => $e->getMessage()]);
            }

        } else {

            return response(['status' => 'error', 'errmsg' => '该数据不存在']);
        }
    }
}
