<?php
/**
* 附件管理类
 */
namespace App\Http\Controllers;

use App\Attachment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Filesystem\FilesystemManager;

class AttachmentController extends Controller
{
    protected $model;
    protected $filesystem;

    public function __construct(Attachment $atta, FilesystemManager $filesystemManager)
    {
        $this->model = $atta;
        $this->filesystem = $filesystemManager;
    }
    /**
    * 文件下载
     */
    public function download($key)
    {
       $item = $this->model->where(['key' => $key])->first();

       if ($item) {
           return response()->download(storage_path('app/public/'.$item->path), $item->name);
       }
       else {
           return '文件不存在';
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
                    return response(['status' => 'success','link' => env('APP_URL').'/index.php/file/download/'.$data['key'],'path' => $path,"id" => $model->id], 201);
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
