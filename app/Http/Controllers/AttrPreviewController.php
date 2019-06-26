<?php

namespace App\Http\Controllers;

use App\PriceVersion;
use App\Attachment;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Filesystem\FilesystemManager;

class AttrPreviewController extends Controller
{
    private $view;
    private $atta;
    private $priceVersion;
    private $filesystemManager;

    public function __construct(ViewFactory $factory, Attachment $atta, FilesystemManager $manager, PriceVersion $priceVersion)
    {
        $this->view = $factory;
        $this->atta = $atta;
        $this->priceVersion = $priceVersion;
        $this->filesystemManager = $manager;
    }

    //厂家价格表预览

    public function PriceFileView(Request $request)
    {
        $version = $this->priceVersion->find($request->id);

        if (!$version->atta_id)
            return "没有文件";


        $blade = 'ARSumAttrViewOffice';
        $file = $this->atta->find($version->atta_id);

        //如果是图片
        if (getImageSize(storage_path('app/public/'.$file->path))) {
            $blade = 'ARSumAttrViewImage';
        }


        return $this->view->make($blade)->with(['url' => env('APP_URL').$this->filesystemManager->url($file->path)]);
    }
}
