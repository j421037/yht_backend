<?php

namespace App\Http\Controllers;

use Excel;
use Auth;
use Miao;
use JWTAuth;
use Hash;
use Captcha;
use App\User;
use App\Role;
use App\Department;
//use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use App\Http\Requests\RegisterFormRequest; 
use App\Http\Requests\ResetPassFormRequst;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Filesystem\FilesystemManager;

class AuthController extends Controller
{
    private $user;
    private $role;
    private $excel;
    private $filesystem;

    public function __construct(User $user, Excel $excel, FilesystemManager $filesystem, Role $role)
    {
        $this->user = $user;
        $this->excel = $excel;
        $this->role = $role;
        $this->filesystem = $filesystem->disk('local');
    }

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

	/**批量导入用户**/
	public function import(Request $request)
    {
        if ($request->hasFile('userfile')) {
            $model = $this->user;//模型
            //保存文件
            $file = $this->filesystem->putFile('file/' . date('Y-m-d', time()),$request->userfile);
            //获取文件路径
            $path = storage_path('app/'.$file);
            //读取文件内容
            $data = Excel::selectSheetsByIndex(0)->load($path, 'UTF-8')->get(['name','phone','password']);

            //保存文件结果
            $result = array();
            try {

                $list = $this->user->whereIn('phone', $data->pluck('phone'))->get()->pluck('phone')->toArray();

                $data->each(function ($items, $index) use ($model, $list, &$result) {
                    //如果没有该用户 再新建
                    if (!in_array($items->phone, $list)) {

                        $item = [];
                        $item['name'] = $items->name;
                        $item['phone'] = $items->phone;
                        $item['authorize'] = 1;
                        $item['password'] = Hash::make(trim($items->password));

                        $user = $model->create($item);
                        unset($item['password']);

                        if ($user) {
                            $item['status'] = 1;
                            $item['text'] = '导入成功';
                        } else {
                            $item['status'] = 0;
                            $item['text'] = '导入失败';
                        }

                        array_push($result, $item);

                        //分配基础角色
                        $role = $this->role->where(['name' => '基础功能'])->first();
                        $user->role()->sync($role->id);
                    }
                });
            }
            catch (QueryException $e)
            {

            }

            return response(['status' => 'success', 'result' => $result],200);
        }
    }

    /**禁用用户**/
    public function UserDisable(Request $request)
    {
        try {

            if ($request->id) {
                //不能是管理员
                if ($this->UserIsAdmin($request->id)) {
                    throw new \Exception('不能禁用该用户');
                }

                $user = $this->user->find($request->id);
                $user->authorize = 0;

                if ($user->save()) {
                    return response(['status' => 'success'], 200);
                }
            }
            else {
                throw new \Exception('请求不合法');
            }
        }
        catch (\Exception $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }

    /**删除用户**/
    public function UserDelete(Request $request)
    {
        try {

            if ($request->id) {
                //不能是管理员
                if ($this->UserIsAdmin($request->id)) {
                    throw new \Exception('不能删除该用户');
                }

                $user = $this->user->find($request->id);

                if ($user->delete()) {
                    return response(['status' => 'success'], 200);
                }
            }
            else {
                throw new \Exception('请求不合法');
            }
        }
        catch (\Exception $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}
