<?php

namespace App\Http\Controllers;

use App\ReceivablePlan;
use App\ArrearsData;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Resources\RecePlanResource;
use App\Http\Requests\ReceivablePlanStoreRequest;

class ReceivablePlanController extends Controller
{
    private $model;
    private $arrear;

    public function __construct(ReceivablePlan $plan,ArrearsData $arrearsData)
    {
        $this->model = $plan;
        $this->arrear = $arrearsData;
    }

    public function store(ReceivablePlanStoreRequest $request)
    {
    	$data = $request->all();
    	$data['content'] = trim($data['content']);
    	$data['week'] = date('W', strtotime($data['date']));
    	$data['user_id'] = $this->getUserId();

    	$arr = $this->arrear->find($request->rid);

    	if (!$arr && $arr->user_id != $data["user_id"]) {
    		return response(['status' => 'err','errmsg' => '没有权限操作该项目'], 200);
    	}

    	try {

			if ($this->model->create($data)) {
				return response(['status' => 'success'], 201);
			}

    	} catch( QueryException $e) {
    		return response(['status' => 'err', 'errmsg' => $e->getMessage()]);
    	}
    }

    public function all(Request $request)
    {
        $limit = $request->limit ?? 5;
        $offset = $request->offset ?? 0;
        $where = ['rid' => $request->rid];

        $row = $this->model->where($where)->limit($limit)->offset($offset)->orderBy('id', 'desc')->get();
        $total = $this->model->where($where)->count();

        return response(['row' => RecePlanResource::collection($row), 'total' => $total], 200);
    }

    public function update(Request $request)
    {
        $recePlan = $this->model->find($request->id);

        try {
            $recePlan->content = $request->post('content');
            $recePlan->week = date('W', strtotime($request->date));
            $recePlan->date = $request->date;

            if ($recePlan->save()) {
                return response(['status' => 'success'], 200);
            }
        }
        catch(QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
    public function del(Request $request)
    {
        try {
            if ($this->model->destroy($request->id)) {
                return response(['status' => 'success'], 200);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}
