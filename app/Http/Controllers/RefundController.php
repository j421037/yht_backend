<?php
/**
* 退货单类
* @author 王鑫
* 2018-10-16
*/
namespace App\Http\Controllers;

use App\InitialAmount;
use Auth;
use JWTAuth;
use App\User;
use App\Refund;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\ARSumRole;
use Illuminate\Database\QueryException;
use App\Http\Requests\RefundStoreRequest;
use App\Http\Requests\RefundUpdateRequest;
use App\Http\Resources\RefoundResource;

class RefundController extends Controller
{
    use ARSumRole;

    private $limit = 5;
    private $offset = 0;
    /**需要返回的数据**/
    private $row;
    /**总记录数**/
    private $total;

    private $user;

    private $hasRole;
    private $model;
    private $initialAmount;

    public function __construct(User $user, InitialAmount $initialAmount, Refund $refund)
    {
        $this->user = $user;
        $this->model = $refund;
        $this->initialAmount = $initialAmount;
        $this->hasRole = $this->checkRole($this->getUser()->id);
    }

    public function store(RefundStoreRequest $request)
    {
    	try {
            if (!$this->hasRole)
                throw new \Exception("没有权限操作该对象");

            $data = $request->all();
            $data["date"] = strtotime($data["date"]);
            $initial = $this->initialAmount->where(["rid" => $request->rid])->orderBy("date","desc")->first();

    	    if ($initial && $initial->date >= $data["date"])
    	        throw new \Exception("退货单必须在期初日期 ".date("Y-m-d",$initial->date)." 之后");

    	    $result = $this->model->create($data);

    	    if ($result)
    	        return response(["status" => "success"], 201);
    	}
    	catch(QueryException $e) {
    		return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
    	}
        catch(\Exception $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
        }
    }

    public function update(RefundUpdateRequest $request)
    {
        try {
            if (!$this->hasRole)
                throw new \Exception("没有权限操作该对象");

            $row = $this->model->find($request->id);
            $date = strtotime($request->date);
            $initial = $this->initialAmount->where(["rid" => $request->rid])->orderBy("date","desc")->first();

            if (!$row)
                throw new \Exception("目标不存在");
            if ($date <= $initial->date)
                throw new \Exception("退货单必须在期初日期 ".date("Y-m-d",$initial->date)." 之后");

            $row->amountfor = $request->amountfor;
            $row->date = $date;
            $row->remark = $request->remark;

            if ($row->save())
                return response(["status" => "success"], 200);
        }
        catch(QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
        }
        catch(\Exception $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
        }
    }

    /**
     * 设置属性
     * @param string $name
     * @param $value
     **/
    private function _set($name, $value)
    {
        if ($value) {
            $this->$name = $value;
        }
    }

    /**
     *退货列表
     */
    public function all(Request $request)
    {
        $this->_set('limit', $request->limit);
        $this->_set('offset', $request->offset);
        $this->row = Refund::where(['rid' => $request->rid])->limit($this->limit)->offset($this->offset)->orderBy('date', 'desc')->get();
        $this->total = Refund::where(['rid' => $request->rid])->count();

        return response(['row' => RefoundResource::collection($this->row), 'total' => $this->total], 200);
    }

    /**
     * 删除退货
     */
    public function del(Request $request)
    {

        try {
            if (Refund::destroy($request->id)) {
                return response(['status' => 'success'], 200);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
        }
    }


}
