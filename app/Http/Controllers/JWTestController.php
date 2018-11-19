<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use JWTAuth;

class JWTestController extends Controller
{
    //
    public function index(Request $request) 
    {
    	// $code = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEzLCJpc3MiOiJodHRwOi8vMTkyLjE2OC4xLjI0MS9sYXJTeXMvcHVibGljL2luZGV4LnBocC9hcGkvYXV0aC9sb2dpbiIsImlhdCI6MTUyNTY4NTc2NiwiZXhwIjoxNTI1NzIxNzY2LCJuYmYiOjE1MjU2ODU3NjYsImp0aSI6Ijk4V1A2SkxEeUhnV29mUTcifQ.BTf5dQi3IjKkL_VuD1Qu4jGebKIaG9-lUUmsjgmHxX4';
    	echo '<pre>';
    	// var_dump(JWTAuth::getPayload($code));
    	// echo time();
    	// var_dump(JWTAuth::auth->setRequest($request));
    	$token = JWTAuth::getToken();
    	var_dump($request->headers->get('authorization'));
    }
}
