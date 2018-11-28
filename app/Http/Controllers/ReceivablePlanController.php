<?php

namespace App\Http\Controllers;

use Auth;
use App\Project;
use App\ReceivablePlan;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Resources\RecePlanResource;
use App\Http\Requests\ReceivablePlanStoreRequest;

class ReceivablePlanController extends Controller
{
    public function store(ReceivablePlanStoreRequest $request)
    {
    	$data = $request->all();
    	$data['content'] = trim($data['content']);
    	$data['week'] = date('W', strtotime($data['date']));
    	$data['user_id'] = Auth::user()->id;
    	
    	$project = Project::where(['id' => $data['pid'], 'user_id' => $data['user_id']])->first();

    	if (!$project) {
    		return response(['status' => 'err','errmsg' => '没有权限操作该项目'], 200);
    	}

    	try {

			if (ReceivablePlan::create($data)) {
				return response(['status' => 'success'], 200);
			}

    	} catch( QueryException $e) {
    		return response(['status' => 'err', 'errmsg' => $e->getMessage()]);
    	}
    }

    public function all(Request $request)
    {
        $limit = $request->limit ?? 5;
        $offset = $request->offset ?? 0;
        $where = ['pid' => $request->pid];

        $row = ReceivablePlan::where($where)->limit($limit)->offset($offset)->orderBy('id', 'desc')->get();
        $total = ReceivablePlan::where($where)->count();

        return response(['row' => RecePlanResource::collection($row), 'total' => $total], 200);
    }

    public function update(Request $request)
    {
        $recePlan = ReceivablePlan::find($request->id);

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
            if (ReceivablePlan::destroy($request->id)) {
                return response(['status' => 'success'], 200);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}
