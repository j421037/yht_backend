<?php
/**
 * @author 王鑫
 * 应收合同附件预览
 */
namespace App\Http\Controllers;

use App\Attachment;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Filesystem\FilesystemManager;


class ARSumAttrViewController extends Controller
{
    protected $view;
    protected $atta;
    protected $file;
    protected $config;
    protected $filesystemManager;

    public function __construct(ViewFactory $view, Attachment $atta, FilesystemManager $filesystemManager)
    {
        $this->view = $view;
        $this->atta = $atta;
        $this->filesystemManager = $filesystemManager;
    }

    public function index(Request $request)
    {
        $blade = 'ARSumAttrViewOffice';
        $this->file = $this->atta->find($request->id);
        //如果是图片
        if (getImageSize(storage_path('app/public/'.$this->file->path))) {
            $blade = 'ARSumAttrViewImage';
        }

        return $this->view->make($blade)->with(['url' => env('APP_URL').$this->filesystemManager->url($this->file->path)]);
    }
}
