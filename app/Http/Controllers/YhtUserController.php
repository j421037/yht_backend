<?php

namespace App\Http\Controllers;

use App\YhtUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class YhtUserController extends Controller
{

    public function user()
    {
    	$list = YhtUser::all();

    	foreach ($list as $v) {

    	    if ($v->phone && !User::where(['phone' => $v->phone])->first()) {
    	    	
    			User::create(['name' => $v->real_name, 'phone' => $v->phone, 'password' => Hash::make('szyhtjc')]);
    		}

    		// User::create(['name' => $v->real_name, 'phone' => $v->phone, 'password' => Hash::make('szyhtjc')]);
    		// return $v->phone;
    	}

    }
}
