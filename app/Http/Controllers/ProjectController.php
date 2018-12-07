<?php

namespace App\Http\Controllers;

use Auth;
use Storage;
use App\Project;
use App\Attachment;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Resources\ProjectQueryResource;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
    	$data = $request->all();
    	$data['user_id'] = Auth::user()->id;
       
        try {
           
            if ($request->AgreementAtta) {
                $file = [];
                $Finfo = new \Finfo(FILEINFO_MIME);
                $file['path'] = Storage::disk('local')->putFile('file/'.date('Y-m-d',time()), $request->AgreementAtta);
                $file['name'] = $request->AgreementAtta->getClientOriginalName();
                $file['mime'] = $Finfo->file(storage_path('app/'.$file['path']));

                if ($atta = Attachment::create($file)) {
                    $data['attachment_id'] = $atta->id;
                }
            }

            //确保 客户名称、施工范围相同的情况下，必须是同一个用户才能新建标签不一样的客户
            if ($project = Project::where(['name' => $data['name'],'tid' => $data['tid']])->first()) {
                if ($project->user_id != $this->getUserId()) {
                    return response(['status' => 'error','errmsg' => '无权新建该项目']);
                }
            }

    		if (Project::create($data)) {
    			return response(['status' => 'success']);
    		}
    	} catch (QueryException $e) {
            $errmsg = $e->getMessage();
            //23000  唯一索引错误
            if ($e->getCode() == 23000) {
                $errmsg = "该项目已经存在";
            }

    		return response(['status' => 'err', 'errmsg' => $errmsg]);
    	}
    }

    /**查询项目**/
    public function query(Request $request)
    {
    	$list = [];

    	if ($request->keyword != '') {

	    	$list = Project::where('name','like','%'.trim($request->keyword).'%')->get();

	    	$list = ProjectQueryResource::collection($list);
	    }

    	return response(['data' => $list], 200);
    }
}
