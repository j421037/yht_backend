<?php

namespace App\Http\Controllers;

use App\Deparment;
use Illuminate\Http\Request;

class DeparmentController extends Controller
{
    public function store(Request $request)
    {	
    	if (Deparment::create(['name' => $request->name])) {

    		return response(['status' => 'success'], 200);
    	}
    }
}
