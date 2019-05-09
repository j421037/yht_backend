<?php
/**
* 退货单类
* @author 王鑫
* 2018-10-16
*/
namespace App\Http\Controllers;

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

    private  $user;

    private $hasRole;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->hasRole = $this->checkRole($this->getUser()->id);
    }

    public function store(RefundStoreRequest $request)
    {
    	try {

    		$data = $request->all();
    		$data['date'] = strtotime($data['date']);

    		if (Refund::create($data)) {
    			return response(['status' => 'success'], 200);
    		}

    	} catch(QueryException $e) {

    		return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
    	}
    }

    public function update(RefundUpdateRequest $request)
    {
        $model = Refund::find($request->id);

        if ($model && $this->hasRole) {
            $model->date    = strtotime($request->date);
            $model->refund  = $request->refund;
            $model->remark  = $request->remark;

            try {
                if ($model->save()) {
                    return response(['status' => 'success']);
                }
            } catch (QueryException $e) {
                return response(['status' => $e->getMessage()]);
            }

        } else {
            return response(['status' => 'error', 'errmsg' => '该数据不存在']);
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
