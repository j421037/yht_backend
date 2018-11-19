<?php
/**
* 公共客户资源控制器类
* 2018-06-20
*/

namespace App\Http\Controllers;

use Auth;
use App\Customer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerPubResource;
use Illuminate\Support\Facades\DB; 

class CustomerPubController extends Controller
{
	/**
	* 资源列表
	*/
    public function list(Request $request)
    {
        $departmentId = null;

    	if ($request->type != 0) {

            $user = User::find(Auth::user()->id);
            $departmentId = $user->department_id;
        }

        
        $data = $this->getData($request->offset, $request->limit, $departmentId);
    	
    	$loadAll = false;

    	$nextOne = $request->offset + $request->limit;

    	$next =$this->getData($nextOne, $request->limit, $departmentId);


    	if ( count($data) < 1 || count($next) < 1) {

    		$loadAll = true;
    	} 

        // 把resource转换成实体属性
        // $json = response()->json(CustomerPubResource::collection($data));
        // //getcontent 获取response中的content 属性
        // $list = json_decode($json->getContent(),true);
        // //创建一个collection类 然后再次排序
        // $collect = collect($list);

    	return response(['data' => CustomerPubResource::collection($data)->sortByDesc('sort'), 'loadAll' => $loadAll, 'next' => $next], 200);
    }

    protected function getData( $offset, $limit, $departmentId = null)
    {
        $data = Customer::where(['customers.user_id' => null, 'customers.publish' => 1, 'customers.department_id' => $departmentId,'customer_notes.action' => 3])
                            ->join('customer_notes','customers.id', '=', 'customer_notes.customer_id')
                            ->select('customers.*','customer_notes.created_at as sort')
                            ->orderBy('sort', 'desc')
                            ->offset($offset)
                            ->limit($limit)
                            ->get();

        return $data;
    }
}
