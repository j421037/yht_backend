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
use App\CustomerTrack;
use App\CustomerTag;
use App\CustomerRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RealCustomerAddRequest;
use App\Http\Requests\RealCustomerRequest;
use App\Http\Requests\CustomerTrackRequest;
use App\Http\Requests\CustomerTagRequest;
use App\Http\Requests\CustomerRecordRequest;
use Illuminate\Database\QueryException;
use App\Http\Resources\RealCustomerQueryResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\RealCustomerResource;
use App\Http\Resources\CustomerTrackResource;
use App\Http\Resources\CustomerTagResource;
use App\Http\Resources\CustomerRecordResource;

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
            if (!$v->name || !$v->user || strtotime($v->date) < 1)
                continue;

            $user = User::where(['name' => trim($v->user)])->first();

            if (!$user) 
                continue;

            $cusItem = ['name' => trim($v->name), 'user_id' => $user->id, 'status' => $statusItemId];
            $proItem = [];

            if ($v->project) {
                $proItem = [
                    'name' => trim($v->project),
                    'user_id' => $user->id,
                    'payment_days' => trim($v->payment_days),
                    'type' => trim($v->type),
                    'tax' => trim($v->tax),
                    'agreement' => trim($v->agreement),
                    'tid' => $tid,
                    'tag' => $this->GetEnumberItemFormName($v->tag),
                    'affiliate' => trim($v->affiliate),//挂靠信息
                    'estimate' => trim($v->estimate), //预计金额
                    'phone_num' => trim($v->phone_num), //联系电话
                ];
            }
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
                $rece = [
                    'cust_id'   => $result->id,
                    'amountfor' => $receItem['init_amountfor'],
                    'is_init'   => 1,
                    'date'      => strtotime($receItem['date']),
                    'remark'    => $receItem['remark'],
                    'pid'       => 0
                ];

                if (count($proItem) > 0) {
                    $proItem['cust_id'] = $result->id;
                    $project = Project::create($proItem);
                    $rece['pid'] = $project->id;
                }

                if (AReceivable::create($rece)) {
                    DB::commit();
                    return true;
                }
            }
            else {
                return false;
            }
        }
        catch (\Illuminate\Database\QueryException $e) {
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

	//获取枚举值
	public function getEnum(Request $request)
	{
		try {
			if ($id = Enumberate::where(['name' => $request->value])->first()->id) {
				return EnumberateItem::where(['eid' => $id])->get();
			}
			
        }
        catch (QueryException $e) { 
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
	}
	
	//项目详情
	public function getProject(Request $request)
	{
		try {

			$list = Project::where(['id' => $request->pid])->get();
			$customer = RealCustomer::where(['pid' => $request->pid])->get();
            //return response(['row' => ProjectResource::collection($list), 'customer' => RealCustomerResource::collection($customer)], 200);
            return response(['row' => ProjectResource::collection($list), 'customer' => RealCustomerResource::collection($customer)], 200);
        }
        catch (QueryException $e) { 
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
	}

    public function all(Request $request)
    {
        $limit = $request->limit ?? 5;
		$offset = (intval($request->pagenow) - 1 ) * $limit;
		$type = $request->type ?? 0;
        try {
			if($type == 0){
				$model = RealCustomer::whereIN('user_id', array_keys($this->UserAuthorizeCollects()))
						->offset($offset)
						->limit($limit)
						->orderBy('id', 'desc');

				$list = $model->get();
				$total = count( RealCustomer::whereIN('user_id', array_keys($this->UserAuthorizeCollects()))->get());
				

				
			} else {
				$limit = intVal($request->pagesize);
				$offset = (intval($request->pagenow) - 1 ) * $limit;

				$model = RealCustomer::where(['type' => $type])->whereIN('user_id', array_keys($this->UserAuthorizeCollects()))
						->offset($offset)
						->limit($limit)
						->orderBy('id', 'desc');

				$list = $model->get();
				$total = count( RealCustomer::where(['type' => $type])->whereIN('user_id', array_keys($this->UserAuthorizeCollects()))->get());
				
			}

            return response(['row' => RealCustomerResource::collection($list), 'total' => $total], 200);
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
	
	//项目详情
	public function getProjects(Request $request)
	{
		try {

			$list = Project::where(['cust_id' => $request->id])->get();
            return response(['row' => ProjectResource::collection($list)], 200);
        }
        catch (QueryException $e) { 
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
	}
	
	/**新建客户**/
    public function add(RealCustomerRequest $request)
    {
    	$data = $request->all();
        $data['user_id'] = $this->getUserId();

    	try {
			
			if (RealCustomer::where(['name' => $request->name, 'phone' => $request->phone])->first()) {
				return response(['status' => 'error', 'errmsg' => '客户已存在'], 200);
			}

            if (RealCustomer::where(['name' => $request->name,'work_scope' => $request->work_scope, "pid" => $request->pid])->first()) {
                return response(['status' => 'error', 'errmsg' => '施工范围客户已存在'], 200);
            }

            $result = RealCustomer::create($data);

    		if ($result) {
    			return response(['status' => 'success', 'id' => $result->id], 201);
    		}

    	} catch (\Illuminate\Database\QueryException $e) {

    		$msg = $e->getMessage();

    		return response(['status' => 'error', 'errmsg' => $msg], 200);
    	}
    }
	
	/**新建标签**/
    public function addTag(CustomerTagRequest $request)
    {
    	$data = $request->all();
        $data['user_id'] = $this->getUserId();

    	try {
			
            $result = CustomerTag::create($data);

    		if ($result) {
    			return response(['status' => 'success', 'id' => $result->id], 200);
    		}

    	} catch (\Illuminate\Database\QueryException $e) {

    		$msg = $e->getMessage();

    		return response(['status' => 'error', 'errmsg' => $msg], 200);
    	}
    }
	
	/**新建跟踪**/
    public function addTrack(CustomerTrackRequest $request)
    {
    	$data = $request->all();
        $data['user_id'] = $this->getUserId();

    	try {
			
            $result = CustomerTrack::create($data);

    		if ($result) {
    			return response(['status' => 'success', 'id' => $result->id], 200);
    		}

    	} catch (\Illuminate\Database\QueryException $e) {

    		$msg = $e->getMessage();

    		return response(['status' => 'error', 'errmsg' => $msg], 200);
    	}
    }
	
	/**新建记录**/
    public function addRecord(CustomerRecordRequest $request)
    {
    	$data = $request->all();
        $data['user_id'] = $this->getUserId();

    	try {
			
            $result = CustomerRecord::create($data);

    		if ($result) {
    			return response(['status' => 'success', 'id' => $result->id], 200);
    		}

    	} catch (\Illuminate\Database\QueryException $e) {

    		$msg = $e->getMessage();

    		return response(['status' => 'error', 'errmsg' => $msg], 200);
    	}
    }
	
	/**更新客户信息**/
    public function updateInfo(Request $request)
    {
        $realCustomer = RealCustomer::find($request->id);
        if (RealCustomer::where(['name' => $request->name, 'work_scope' => $request->work_scope])->first()) {
            return response(['status' => 'error', 'errmsg' => '施工范围客户已存在'], 200);
        }
        try {
            $realCustomer->name = trim($request->name);
			$realCustomer->phone = trim($request->phone);
			$realCustomer->work_scope = $request->work_scope;
			$realCustomer->project_type = $request->project_type;
			$realCustomer->attached = $request->attached;
			$realCustomer->contract = $request->contract;
			$realCustomer->account_period = $request->account_period;
			$realCustomer->tax = trim($request->tax);
			$realCustomer->coop = $request->coop;
			$realCustomer->level = $request->level;


            if ($realCustomer->save()) {
                return response(['status' => 'success']);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
	
	//动态跟踪
	public function getInfo(Request $request)
	{
		try {
			$list = RealCustomer::where(['id' => $request->id])->get();
			
            return response(['row' => RealCustomerResource::collection($list)], 200);
        }
        catch (QueryException $e) { 
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
	}
	
	//动态跟踪
	public function getTrack(Request $request)
	{
		try {
			$list = CustomerTrack::where(['cust_id' => $request->cust_id])->get();
			
            return response(['row' => CustomerTrackResource::collection($list)], 200);
        }
        catch (QueryException $e) { 
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
	}
	
	//标签
	public function getTag(Request $request)
	{
		try {

			$list = CustomerTag::where(['cust_id' => $request->cust_id])->get();
			$record = CustomerRecord::where(['cust_id' => $request->cust_id])->get();
            return response(['tag' => CustomerTagResource::collection($list), 'record' => CustomerRecordResource::collection($record)], 200);
        }
        catch (QueryException $e) { 
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
	}
	
	
	/**查询客户**/
    public function search(Request $request) 
    {	

    	if ($request->keyword != '') {

            $list = Project::where('name','like','%'.trim($request->keyword).'%')->get();
	    	$list = ProjectResource::collection($list);
	    }
		return response(['data' => $list], 200);
    }

    /**查询客户**/
    public function SearchCust(Request $request)
    {
        $list = [];
        if ($request->keyword != '') {
            $list = RealCustomer::where('name','like','%'.trim($request->keyword).'%')->get();
        }

        return response(["data" => $list], 200);
    }
}
