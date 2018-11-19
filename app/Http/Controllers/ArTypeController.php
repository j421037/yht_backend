<?php

namespace App\Http\Controllers;

use App\ARType;
use Illuminate\Http\Request;
use App\Http\Resources\ArTypeResource;
use Illuminate\Database\QueryException;
use App\Http\Requests\ArTypeStoreRequest;

class ArTypeController extends Controller
{
    //
    public function index() {
    	$list = ARType::all();
    	$list = ArTypeResource::collection($list);

    	return response(['data' => $list], 200);
    }

    public function store(ArTypeStoreRequest $request)
    {
    	try {
    		if (ARType::create($request->all())) {
    			return response(['status' => 'success']);
    		}
    	} catch (QueryException $e) {
    		return response(['status' => 'err', 'errmsg' => $e->getMessage()]);
    	}
    }
}
