<?php

namespace App\Http\Controllers;

use Auth;
use Storage;
use App\Project;
use App\Attachment;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Requests\ProjectAddRequest;
use App\Http\Resources\ProjectQueryResource;
use App\Http\Resources\ProjectResource;
use Illuminate\Filesystem\FilesystemManager;

class ProjectController extends Controller
{
	protected $model;
    protected $filesystem;

    public function __construct(Attachment $atta, FilesystemManager $filesystemManager)
    {
        $this->model = $atta;
        $this->filesystem = $filesystemManager;
    }
	
    public function store(Request $request)
    {
    	$data = $request->all();
    	$data['user_id'] = Auth::user()->id;
       
        try {
           
            if ($request->AgreementAtta) {
                $file = [];
                $Finfo = new \Finfo(FILEINFO_MIME);
                $file['path'] = Storage::disk('public')->putFile('arsum/'.date('Y-m-d',time()), $request->AgreementAtta);
                $file['name'] = $request->AgreementAtta->getClientOriginalName();
                $file['mime'] = $Finfo->file(storage_path('app/public/'.$file['path']));

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
	
	public function all(Request $request)
    {
        $limit = $request->limit ?? 5;
		$offset = (intval($request->pagenow) - 1 ) * $limit;
        try {
			$model = Project::whereIN('user_id', array_keys($this->UserAuthorizeCollects()))
					->offset($offset)
					->limit($limit)
					->orderBy('id', 'desc');

			$list = $model->get();
			$total = count(Project::whereIN('user_id', array_keys($this->UserAuthorizeCollects()))->get());
			return response(['row' => ProjectResource::collection($list), 'total' => $total], 200);
				
		} catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
	
	//搜索项目提示
	public function getProjectBySearch(Request $request)
	{
		try {
			if ($request->keyword != '') {

				$list = Project::where('name','like','%'.trim($request->keyword).'%')->get();
				if ($list) {
					return response(['row' => ProjectResource::collection($list)], 200);
				} else {
					return null;
				}
			}		
            
        }
        catch (QueryException $e) { 
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
	}
	
	/**新建项目**/
    public function add(ProjectAddRequest $request)
    {
    	$data = $request->all();
        $data['user_id'] = $this->getUserId();

    	try {
    	    $result = Project::where(['name' => $request->name])->first();

			if ($result) {
				return response(['status' => 'error', 'errmsg' => '项目已存在', 'id' => $result->id], 200);
			}

            $result = Project::create($data);

    		if ($result) {
    			return response(['status' => 'success', 'id' => $result->id], 200);
    		}

    	} catch (\Illuminate\Database\QueryException $e) {

    		$msg = $e->getMessage();

    		return response(['status' => 'error', 'errmsg' => $msg], 200);
    	}
    }
	
	/**
    * 文件上传
     */
    public function upload(Request $request)
    {
        $Finfo = new \Finfo(FILEINFO_MIME);

        if ($file = $request->uploadfile) {
            $path = $this->filesystem->disk('public')->putFile('attachment/'.date('Y-m-d', time()), $file);

            $data = [];
            $data['path'] = $path;
            $data['name'] = $file->getClientOriginalName();
            $data['key'] = md5(uniqid());
            $data['mime'] = $Finfo->file(storage_path('app/public/'.$path));

            try {
                if ($model = $this->model->create($data)) {
                    return response(['status' => 'success','link' => env('APP_URL').'/index.php/file/download/'.$data['key'],'path' => $path, 'id' => $model->id], 201);
                }
            }
            catch (QueryException $e) {
                return response(['status' => 'error','errcode' => $e->getCode(), 'errmsg' => '文件上传失败']);
            }

        }
        else {
            return response(['status' => 'error','errmsg' => '上传文件不能为空']);
        }
    }
}
