<?php
/**
* 公共客户资源控制器类
* 2018-06-20
*/

namespace App\Http\Controllers;

use App\RealCustomer;
use App\User;
use App\Customer;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerPubResource;
use Illuminate\Support\Facades\DB;

class CustomerPubController extends Controller
{
    private $user;
    private $customer;
    private $realCustomer;

    public function __construct(User $user, Customer $customer, RealCustomer $realCustomer)
    {
        $this->user = $user;
        $this->customer = $customer;
        $this->realCustomer = $realCustomer;
    }

    /**
	* 资源列表
	*/
    public function list(Request $request)
    {
        $user_id = $this->getUserId();
        $limit = $request->limit or 0;
        $offset = $request->offset or 0;
        $loadAll = false;

        $customer = $this->customer->where(["publish" => 1,"accept" => 0, "user_id" => null]);
        $customers = $customer->limit($limit)->offset($offset)->orderBy("updated_at", "desc")->get();
        $total = $customer->count();

        if (($limit + $offset) > $total) {
            $loadAll = true;
        }

    	return response(['data' => CustomerPubResource::collection($customers), 'loadAll' => $loadAll,"total" => $total,"limit" => $limit,"offset" => $offset], 200);
    }

    /**
     * 客户升级
     */
    public function upgrade(Request $request)
    {
        DB::beginTransaction();

        $data = $request->all();
        $data["user_id"] = $this->getUserId();

        try {

            $r0 = $this->realCustomer->where(['name' => $request->name, 'phone' => $request->phone])->first();

            if ($r0) {
                throw new \Exception("客户已经存在");
            }

            $r1 = $this->realCustomer->where(['name' => $request->name,'work_scope' => $request->work_scope, "pid" => $request->pid])->first();

            if ($r1) {
                throw new \Exception("施工范围客户已存在");
            }

            $realCustomer = $this->realCustomer->create($data);
            $customer = $this->customer->find($request->customer_id);
            $customer->real_customer_id = $realCustomer->id;
            $customer->real_project_id = $realCustomer->pid;

            if ($customer->save()) {
                DB::commit();

                return response(["status" => "success"], 201);
            }
        }
        catch (QueryException $qe) {
            DB::rollBack();
            return response(["status" => "error","errmsg" => $qe->getMessage()], 200);
        }
        catch(\Exception $e) {
            response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
        }
    }

}
