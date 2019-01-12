<?php

namespace App\Http\Controllers;

use App\Department;
use Auth;
use Miao;
use JWTAuth;
use Hash;
use Captcha;
use App\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use App\Http\Requests\RegisterFormRequest; 
use App\Http\Requests\ResetPassFormRequst;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function register(RegisterFormRequest $request)
	{
		// return false; //停止注册
	    $user = new User;

	    $user->name = $request->name;

	    $user->phone = $request->phone;

	    $user->password = Hash::make(trim($request->password));
	    
	    try {
	    	//开启事务
	    	DB::beginTransaction(); 

	    	if ($user->save()) {

	    		//同步企业号用户信息
	    		$result = Miao::createUser($user->phone, $user->name);

	    		if ($result['errcode'] == 0 ) {

	    			DB::commit();

	    			return response([
				        'status' => 'success',
				        'data' => $user
				       ], 200);
	    		}

	    	} else {

	    		DB::rollBack();
	    	}

	    } catch(\Illuminate\Database\QueryException $e) {

	    	$error = $e->getMessage();

	    	if ($e->getCode() == 23000) {

	    		$error = '该账号已存在';
	    	} 

	    	DB::rollBack();

	    	return response([
		        'status' => 'fail',
		        'error' => $error
		       ], 200);
	    }
	    

	    
	}

	public function login(Request $request)
	{
	    $credentials = $request->only('phone', 'password');

	    if ( ! $token = JWTAuth::attempt($credentials)) {
	            return response([
	                'status' => 'error',
	                'error' => '登录失败',
	                'msg' => '账号密码错误'
	            ], 200);
	    }


	    $user  = Auth::getLastAttempted();

	    
	     if ($user->authorize == 0 ) {
	    	//还未审核
	    	 return response([
	                'status' => 'error',
	                'error' => '登录失败',
	                'msg' => '账号还未审核，请联系管理员'
	            ], 200);
	    }

	    return response(['status' => 'success', 'token' => $token])
	        ->header('Authorization', $token);
	}

	public function user(Request $request)
	{
        $data = [];
	    $user = User::find($this->getUserId());

	    $data['id'] = $user->id;
	    $data['name'] = $user->name;
	    $data['isadmin'] = $this->isAdmin();

	    if ($de = Department::find($user->department_id)) {
	        $data['department'] = $de->name;
        }

	    return response(['status' => 'success', 'data' => $data], 200);
	}

	/**修改密码**/
	public function resetPass(ResetPassFormRequst $request) 
	{
		
		if ($request->passwd !== $request->checkpasswd) {
			return response(['status' => 'error', 'error' => '两次密码不一致'], 200);
		}

		try {
			$hash = Hash::make(trim($request->passwd));
			$user = User::find(Auth::user()->id)->update(['password' => $hash]);

			if ($user) {
				return response(['status' => 'success'], 200);
			}

		} catch (QueryException $e) {
			return response(['status' => 'error', 'error' => $e->getMessage()], 200);
		}

		
	}

	public function refresh()
	{
	    return response([
	            'status' => 'success',
	        ]);
	}

	/**
	* 注销登录
	* JWTAuth::invalidate(JWTAuth::getToken())  bool
	*/

	public function logout()
	{
		// $token = JWTAuth::getToken();
		// return JWTAuth::parseToken();
		if (JWTAuth::invalidate(JWTAuth::getToken())) {
			return response(['status' => 'success'], 200);
		}

		return response(['status' => 'fail', 'msg' => '服务器繁忙'], 200);
	}

	/**
	* 业务员登录 --验证码
	* 2018-07-23
	*/
	public function OutLogin(Request $request)
	{
		if (Captcha::check_api($request->captcha, $request->key)) {

			$credentials = $request->only('phone', 'password');

			if ( ! $token = JWTAuth::attempt($credentials)) {
	            return response([
	                'status' => 'error',
	                'error' => '账号密码错误'
	            ], 200);
	    	}

	    	$user  = Auth::getLastAttempted();

		    if ($user->authorize == 0) {
		    	//还未审核
		    	 return response([
		                'status' => 'error',
		                'error' => '账号还未审核，请联系管理员'
		            ], 200);
		    }

	    	return response(['status' => 'success', 'token' => $token])
	        ->header('Authorization', $token);

		} else {
			return response(['status' => 'error', 'error' => '验证码不正确'], 200);
		}
		
	}
	/**
	* 外围业务员 注册
	*/
	public function OutSign(RegisterFormRequest $request) 
	{
		$data = [];
		$data['phone']	= $request->phone;
		$data['name'] 	=  $request->name;
		$data['password'] = bcrypt($request->password);

		try {

			if ($user = User::create($data)) {
				//分配一个资源添加的角色 
				$user->role()->sync(14);
				return response(['status' => 'success'], 200);
			}

		} catch(\Illuminate\Database\QueryException $e) {

			if ($e->getCode() == 23000) {
				$msg = '手机号已注册';
			} else {
				$msg = '注册失败';
			}
 
			return response(['status' => 'error', 'error' => $msg], 200);
		}	
		
	}
}
