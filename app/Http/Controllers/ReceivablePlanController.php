<?php

namespace App\Http\Controllers;

use Auth;
use App\Project;
use App\ReceivablePlan;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
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
}
