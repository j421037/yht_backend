<?php
/**
* RBAC 用户管理类
*/

namespace App\Http\Controllers;

use Auth;
use Miao;
use App\User;
use App\Role;
use App\DoLog;
use App\BaseData;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Resources\UserManagerResource;
// use Illuminate\Support\Facades\DB; 
use App\Http\Resources\NavigationResource;

class UserManagerController extends UserBaseController
{
    //初始化页面配置数据
    public function init() 
    {
        $data = array();

        $data['sync_date'] = DoLog::where(['name' => 'update_workwx'])
                                    ->orderBy('id','desc')
                                    ->first()
                                    ->created_at
                                    ->format('Y-m-d H:i:s');

        return response($data, 200);
    }
    /**
    * 用户列表
    * @return json
    */
    public function getList()
    {
    	$data = array();
    	$list = User::with(['role'])->get();

    	return response(['data' => UserManagerResource::collection($list)], 200);
    }

    /**
    * 角色列表
    * @return  json
    */
    public function role(Request $request)
    {
    	$role = Role::select('id','name')->get();

    	$user = User::with(['role'])->where(['id' => $request->post('id')])->first();

    	$hasRole = array();

    	foreach ($user->role as $k => $v) {
    		$hasRole[] = $v->id;
    	}

    	foreach ($role as $k => $v) {

    		if (in_array($v->id, $hasRole)) {

    			$role[$k]->checked = true;

    		} else {

    			$role[$k]->checked = false;
    		}

    	}

    	return response(['data' => $role], 200);
    }

    /**更新角色**/
    public function saveRole(Request $request) 
    {
    	$id = $request->post('id');

    	$roleId = $request->post('rid');

    	$user = User::find($id);

    	try {
    		if ($user->role()->sync($roleId)) {

    			return response(['status' => 'success'], 200);
    		}

    		return response(['status' => 'error'], 200);

    	} catch( Exception $e) {
    		return response(['status' => 'error', 'msg' => $e->getMessage()], 200);
    	}
    }

    /**
    * 用户对应的导航
    * @return json 
    */
    public function navigation()
    {

        $list = $this->_userNavigation();

        $data = NavigationResource::collection($list)->sortBy('pc_sort')->values()->all();

    	return response(['data' => $data], 200);
    }

    /**
    *审核用户
    *@param $id userid 
    *
    */
    public function UserAllow(Request $request)
    {   
        try {

            //if (在微信企业号中添加一个userid) =>  成功  更新数据库中的authorize

            $user  = User::find($request->id);  

            if( User::where(['id' => $request->id])->update(['authorize' => 1]) != false) {

                return response(['status' => 'success'], 200);

            }
        } catch(\Illuminate\Database\QueryException $e)  {

            return response(['status' => 'error', 'error' => $e->getMessage()], 200);
        }
    }

    /**
    * 同步企业微信通讯录
    */
    public function updateUserFromWorkwx()
    {
        $wxlist = collect(Miao::simplelist())->pluck('userid');

        try {

            if (User::whereIn('phone',$wxlist)->update(['workwx' => 1]) !== false) {

                //写入日志
                DoLog::create(['name' => 'update_workwx']);

                return response(['status' => 'success'], 200);
            }

        } catch(\Illuminate\Database\QueryException $e) {

            return response(['status' => 'error', 'error' => $e->getMessage()], 200);
        }
    }

    /**
    * 将用户加入企业微信
    */

    public function joinWorkWx(Request $request)
    {
        $user = User::where(['workwx' => 0,'id' => $request->id])->first();

        $result = Miao::createUser($user->phone, $user->name);

        if ($result['errcode'] == 0) {

            $chatId = BaseData::where(['name' => 'customerChatId'])->first()->value;

            $chatAddUser = Miao::ChatAddUser($chatId, [$user->phone]);

            if ($chatAddUser['errcode'] == 0) {

                $user->workwx = 1;
                $user->save();

                //写入日志
                DoLog::create(['name' => 'add_workwx', 'value' => 'id:'.$user->id.',name:'.$user->name]);

                return response(['status' => 'success'], 200);
            }
        }

        return response(['status' => 'error', 'error' => $result['errmsg']]);

    }

}
