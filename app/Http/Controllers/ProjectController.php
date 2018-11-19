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

    		if (Project::create($data)) {
    			return response(['status' => 'success']);
    		}
    	} catch (QueryException $e) {
    		return response(['status' => 'err', 'errmsg' => $e->getMessage()]);
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
