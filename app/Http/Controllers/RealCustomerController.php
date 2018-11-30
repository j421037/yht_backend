<?php
/**
* 真实客户维护
* @author 2018-09-19
*/
namespace App\Http\Controllers;

use Auth;
use Excel;
use Storage;
use App\User;
use App\RealCustomer;
use App\PotentialCustomer;
use App\Project;
use App\AReceivable;
use App\BindAttr;
use App\Enumberate;
use App\EnumberateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ARSumController;
use App\Http\Requests\RealCustomerAddRequest;
use Illuminate\Database\QueryException;
use App\Http\Resources\RealCustomerQueryResource;

class RealCustomerController extends Controller 
{
    private $excelParam = [
        'name',
        'project',
        'payment_days',
        'type',
        'tax',
        'agreement',
        'user',
        'init',
        'date',
        'affiliate',
        'tag',
        'estimate',
        'phone_num'
    ];
    /**新建客户**/
    public function store(RealCustomerAddRequest $request)
    {
    	$data = $request->all();
        $data['user_id'] = Auth::user()->id;

    	try {

            //添加合作客户
            if ($data['type'] == 1) {
                $result = RealCustomer::create($data);
            }
            else {
                //添加潜在的目标客户
                $result = PotentialCustomer::create($data);
            }

    		if ($result) {
    			return response(['status' => 'success'], 200);
    		}

    	} catch (\Illuminate\Database\QueryException $e) {

    		$msg = $e->getMessage();

    		if ($e->getCode() == 23000) {
    			$msg = '该客户已存在!';
    		}

    		return response(['status' => 'error', 'errmsg' => $msg], 200);
    	}
    }
    /**查询客户**/
    public function query(Request $request) 
    {	
    	$list = [];

    	if ($request->keyword != '') {

    	    if ($request->type == 'cooperate') {
    	        $model = new RealCustomer;
            }
            else {
                $model = new PotentialCustomer;
            }

            $list = $model->where('name','like','%'.trim($request->keyword).'%')->get();
	    	$list = RealCustomerQueryResource::collection($list);
	    }

    	return response(['data' => $list], 200);
    }

    /**导入客户**/
    public function ImportFromExcel(Request $request) 
    {   
        if (empty($request->file)) {
            return ;
        }

        $userid = Auth::user()->id;
        $file = Storage::disk('local')->putFile('file/'.date('Y-m-d',time()), $request->file);
        
        $realpath = storage_path('app/'.$file);

        //$realpath = storage_path('app/template/template.xlsx');

        //sheet1中存放合作客户的内容
        $data = Excel::selectSheetsByIndex(1)->load($realpath, 'UTF-8')->get($this->excelParam);
        //sheet2中存放潜在目标客户的内容

        $fail = [];
        $success = 0;
        $statusItemId = $this->GetEnumberItem('F_CMK_CUSTATUS');
        $tid = $this->GetEnumberItem('F_CMK_PROATTR');

        foreach ($data as $k => $v) {

            if (!$v->name || !$v->project ||!$v->user || strtotime($v->date) < 1) 
                continue;

            $user = User::where(['name' => trim($v->user)])->first();

            if (!$user) 
                continue;

            $cusItem = ['name' => trim($v->name), 'user_id' => $user->id, 'status' => $statusItemId];
            $proItem = [
                'name'          => trim($v->project), 
                'user_id'       => $user->id,
                'payment_days'  => trim($v->payment_days),
                'type'          => trim($v->type),
                'tax'           => trim($v->tax),
                'agreement'     => trim($v->agreement),
                'tid'           => $tid,
                'tag'           => $this->GetEnumberItemFormName($v->tag),
                'affiliate'     => trim($v->affiliate),//挂靠信息
                'estimate'      => trim($v->estimate), //预计金额
                'phone_num'     => trim($v->phone_num), //联系电话
            ];
            $receivable = [
                'init_amountfor' => trim($v->init) ?? 0,
                'is_init' => 1,
                'date'    => trim($v->date),
                'remark'  => trim($v->remark)  
            ];

            $result = $this->_batchStore($cusItem, $proItem, $receivable);

            if ($result) {

                ++$success;

            } else {
                array_push($fail,['name' => $v->name,'project' => $v->project]);
            }
        }

        return response(['status' => 'success', 'success' => $success, 'fail' => $fail]);    

    }

    /**
    *批量增加客户和项目
    * @param $cusItem 客户列表
    * @param $proItem 项目列表 
    */
    protected function _batchStore($cusItem, $proItem, $receItem) 
    {
        DB::beginTransaction();

        try {

            if ($result = RealCustomer::firstOrCreate($cusItem)) {

                $proItem['cust_id'] = $result->id;

                if ($project = Project::create($proItem)) {

                    $rece = [
                        'pid'       => $project->id, 
                        'cust_id'   => $result->id, 
                        'amountfor' => $receItem['init_amountfor'], 
                        'is_init'   => 1,
                        'date'      => strtotime($receItem['date']),
                        'remark'    => $receItem['remark']
                    ];

                    if (AReceivable::create($rece)) {
                        DB::commit();
                        return true;
                    }                     
                } else {
                    return false;
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return false;
        } 
    }
    /**返回绑定属性信息
     * @param $AttrName 属性名称
     */
    protected function GetEnumberItem($AttrKey)
    {
        return EnumberateItem::where(['eid' => Enumberate::where(['id' => BindAttr::where(['key' => $AttrKey])->first()->eid])->first()->id])->first()->id;
    }
    protected function GetEnumberItemFormName($name)
    {
        return EnumberateItem::where(['name' => $name])->first()->id;
    }
     /**下载模板**/
    public function downloadTemp(Request $request)
    {
        $file = storage_path('app/template/template.xlsx');

        $headers = array(
            'Cache-Control'     => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type'      => 'application/force-download',
            'Accept-Range'      => 'bytes',
            'Content-Length'    => FileSize($file),
            'Content-Type'      => 'application/download',
            'Content-Disposition'       => 'attachment;filename=tempalte.xlsx',
            'Content-Transfer-Encoding' => 'binary'
        );

        return response()->download($file, '客户导入模板.xlsx', $headers);
    }

    /**更新客户状态**/
    public function updateStatus(Request $request)
    {
        $realCustomer = RealCustomer::find($request->id);

        try {
            $realCustomer->status = $request->status;

            if ($realCustomer->save()) {
                return response(['status' => 'success']);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}
