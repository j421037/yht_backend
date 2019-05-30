<?php
/**
 * 客户资源发布类
 *  2018-06-12
 */

namespace App\Http\Controllers;

use App\Refund;
use Miao;
use App\User;
use App\Region;
use App\Customer;
use App\CustomerNote;
use App\BaseData;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerReleaseRequest;
use App\Http\Resources\RegionResource;
use App\Http\Resources\RegionChildResource;
use App\Http\Resources\RegionParentResource;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class CustomerReleaseController extends Controller
{
    /**
     * 省级联动
     */
    public function region(Request $request)
    {
        //1 => 中国
        if ($request->id <= 1) {

            $list = Region::where(['parent_id' => $request->id])->get();

            $list = RegionParentResource::collection($list);

        } else {

            $list = Region::with(['child'])->where(['parent_id' => $request->id])->get();

            $list = RegionResource::collection($list);
        }

        return response(['data' => ($list), 'pid' => $request->id], 200);
    }

    /**
     * 创建一个客户
     */
    public function store(CustomerReleaseRequest $request)
    {
        try {

            $data = $request->all();

            $data['create_user_id'] = $this->getUserId();//创建人

            if ($request->province_code && $request->city_code && $request->area_code) {
                $data['province'] = Region::where(['region_code' => $request->province_code])->first()->id;
                $data['city'] = Region::where(['region_code' => $request->city_code])->first()->id;
                $data['area'] = Region::where(['region_code' => $request->area_code])->first()->id;
            }

            if (Customer::create($data)) {

                return response(['status' => 'success'], 200);
            }

        } catch (QueryException $e) {

            if ($e->getCode() == 23000) {
                return response(['status' => 'error', 'errmsg' => '该资源已经存在'], 200);
            }

            return response(['status' => 'error', 'errmsg' => $e->getMessage(), 'code' => $e->getCode()], 200);
        }
    }

    /**
     *返回所有的客户列表
     * 管理员返回所有
     *  部门返回部门自己发布的
     */
    public function list(Request $request)
    {
        $status = $request->status or 0;
        $where = [];

        if ($status == 1) {
            $where = [["user_id", "<>", null]];
        }

        if ($status == 2) {
            $where = ["user_id" => null];
        }

        $model = Customer::where($where);

        if (!$this->isAdmin()) {
           $model = $model->whereIn("create_user_id", $this->UserAuthorizeIds());
        }

        $list = $model->offset($request->offset)->limit($request->limit)->orderBy('id', 'desc')->get();
        $count = $model->count();
        return response(['data' => CustomerResource::collection($list), 'total' => $count, 'offset' => $request->offset, 'limit' => $request->limit,"sex" => $this->UserAuthorizeIds()], 200);
    }

    /**
     * 发布一个客户
     * @param type = 0 发布的类型为公开  type = 1 发布的类型是部门内可见
     */
    public function publish(Request $request)
    {

        try {
            DB::beginTransaction();

            if ($request->type != 0) {

                $user = User::find($this->getUserId());

                // return response($user->department_id);

                $result = Customer::find($request->id)->update(['publish' => 1, 'department_id' => $user->department_id]);

            } else {

                $result = Customer::find($request->id)->update(['publish' => 1]);
            }

            $log = CustomerNote::create(['user_id' => $this->getUserId(), 'customer_id' => $request->id, 'action' => 3]);

            if ($result != false && $log != false) {

                //提交事务
                DB::commit();
                //发送群聊信息
                $chatid = BaseData::where(['name' => 'customerChatId'])->first()->value;

                $message = "<div class=\"gray\">" . date('Y-m-d H:i:s') . "</div><div class=\"highlight\">有新的客户资源发布</div>";
                $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=ww9b0aad574dc2509b&redirect_uri=https://e.yhtjc.com/v2/public/index.php/wx/auth/redirect&response_type=code&scope=SCOPE&agentid=1000002&state=STATE#wechat_redirect';

                Miao::sendChatCardMessage($chatid, '提醒', $message, $url);
                return response(['status' => 'success'], 200);
            }

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response(['status' => 'fail', 'error' => $e->getMessage()], 200);
        }
    }

    /**修改客户**/
    public function update(Request $request)
    {

        try {

            $customer = Customer::find($request->id);

            if (!$customer) {
                return response(['status' => 'error', 'errmsg' => '没有该客户信息']);
            }

            // $customer->name     = $request->name;
            // $customer->phone    = $request->phone;
            // $customer->demand   = $request->demand;
            // $customer->wechat   = $request->wechat;
            // $customer->qq       = $request->qq;
            // $customer->brand_id = $request->brand_id;
            if ($customer->update($request->all())) {
                return response(['status' => 'success'], 200);
            }


        } catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
        }

    }

    public function delete(Request $request)
    {
        if (!$this->isAdmin())
            return response(["status" => "error", "errmsg" => "没有权限操作该对象"], 200);

        Customer::destroy($request->id);

        return response(["status" => "success"], 200);
    }
}
