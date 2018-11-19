<?php
/**
* 外部添加客户控制器类
* 2018 07 21
*/

namespace App\Http\Controllers;

use Auth;
use Miao;
use Captcha;
use App\Region;
use App\User;
use App\Customer;
use App\CustomerNote;
use App\Department;
use App\BaseData;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerOneResource;
use App\Http\Requests\CustomerReleaseRequest;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB; 

class OutCustomerController extends Controller
{
    /**初始化**/
    public function initCapt()
    {
    	//返回验证码 和key
    	$data = [];
    	$data['capt'] = Captcha::create('default', true);

    	return response($data, 200);
    }

    /**
    * 返回个人创建的客户
    */
    public function list(Request $request)
    {
        
        $loadAll = false;
        $nextOne = $request->offset + $request->limit;

        if (User::find(Auth::user()->id)->group == 'admin') {
            $list = Customer::offset($request->offset)->limit($request->limit)->orderBy('id','desc')->get();
            $next = Customer::offset($nextOne)->limit($request->limit)->orderBy('id','desc')->get();
            $count = Customer::count();

        } else {
            $list = Customer::where(['create_user_id' => Auth::user()->id])->offset($request->offset)->limit($request->limit)->orderBy('id','desc')->get();
            $next = Customer::where(['create_user_id' => Auth::user()->id])->offset($nextOne)->limit($request->limit)->orderBy('id','desc')->get();
            $count = Customer::where(['create_user_id' => Auth::user()->id])->count();
        }

        if (count($next) < 1) {
            $loadAll = true;
        }
    	
        return response(['data' => CustomerResource::collection($list), 'total' => $count,'offset' => $request->offset, 'limit' => $request->limit,'loadAll' => $loadAll], 200);
    }

    /**
    * 发布到业务一部
    */
    public function publish(Request $request)
    {

        try {

            DB::beginTransaction();

            $department_id = Department::where(['name' => '业务一部'])->first()->id;
          
            $result = Customer::find($request->id)->update(['publish' => 1, 'department_id' => $department_id]);

            $log = CustomerNote::create(['user_id' => Auth::user()->id,'customer_id' => $request->id, 'action' => 3]);

            if ($result != false && $log != false) {
            
              //提交事务
              DB::commit();
              //发送群聊信息
              $chatid = BaseData::where(['name' => 'customerChatId'])->first()->value;
              
              $message ="<div class=\"gray\">".date('Y-m-d H:i:s')."</div><div class=\"highlight\">有新的客户资源发布</div>";
              $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=ww9b0aad574dc2509b&redirect_uri=https://e.yhtjc.com/v2/public/index.php/wx/auth/redirect&response_type=code&scope=SCOPE&agentid=1000002&state=STATE#wechat_redirect';

              Miao::sendChatCardMessage($chatid, '提醒', $message, $url);
              return response(['status' => 'success'], 200);
            }
        
        } catch(\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response(['status' => 'fail', 'error' => $e->getMessage()], 200);
        }
    }

    /**
    * 获取一个客户资源的信息
    * @param $id
    * @return response
    */
    public function one(Request $request)
    {

        $condition = [
            'id' => $request->id,
            'create_user_id' => Auth::user()->id
        ];

        if (User::find(Auth::user()->id)->group == 'admin') {
            unset($condition['create_user_id']);
        }

    	$data = Customer::where($condition)->first();
    	// return response($data, 200);
        return response(new CustomerOneResource($data), 200);
    }

    /**
    *更新一个客户
    *@param $customer
    *@return response
    */
    public function update(CustomerReleaseRequest $request)
    {
    	try {

            $condition = [
                'id' => $request->id,
                'create_user_id' => Auth::user()->id
            ];

            if (User::find(Auth::user()->id)->group == 'admin') {
                unset($condition['create_user_id']);
            }

            $data = $request->all();

            if ($request->province_code && $request->city_code && $request->area_code) {
                $data['province'] = Region::where(['region_code' => $request->province_code])->first()->id;
                $data['city'] = Region::where(['region_code' => $request->city_code])->first()->id;
                $data['area'] = Region::where(['region_code' => $request->area_code])->first()->id;
            }

    		$result = Customer::where($condition)->first()->update($data);

    		if ($result) {
	    		return response(['status' => 'success'], 200);
	    	}

	    	return response(['status' => 'error'], 200);

    	} catch(\Illuminate\Database\QueryException $e) {

    		return response(['status' => 'error', 'error' => $e->getMessage()], 200);
    	}
    	
    }

    /**
    * 删除一个客户 laravel  软删除
    * @param $id
    * @return response
    */
    public function delete(Request $request)
    {
    	try {

            $condition = [
                'id' => $request->id,
                'create_user_id' => Auth::user()->id
            ];

            if (User::find(Auth::user()->id)->group == 'admin') {
                unset($condition['create_user_id']);
            }

    		$result = Customer::where($condition)->delete();

    		if ($result !== false) {
    			return response(['status' => 'success'], 200);
    		}

    		return response(['status' => 'error', 'error' => '删除失败'], 200);
    	} catch (QueryException $e) {
    		return response(['status' => 'errur', 'error' => $e->getMessage()], 200);
    	}
    }
}
