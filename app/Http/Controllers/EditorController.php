<?php

namespace App\Http\Controllers;

use Auth;
use VueUEditor;
use Storage;
use App\User;
use Illuminate\Http\Request;

class EditorController extends Controller
{
    //ueditor 配置
    public function Init(Request $request)
    {
    	
    	$config = VueUEditor::Config();

    	return response($config, 200);
    }

    /**
    * 图片上传
    */
    public function UploadImage(Request $request)
    {
        $upfile = $request->file;

    	if (!empty($upfile)) {

            $phone = User::find(Auth::user()->id)->phone;
            
            $path = 'image/'.$phone.'/'.date('Y-m-d', time());

            $file = Storage::disk('public')->putFile($path, $upfile);
            $url = Storage::disk('public')->url($file);

            $info = array(
                "state" => 'success',
                "url" => $url,
                "title" => $url,
                "original" => $upfile->getClientOriginalName(),
                "type" => Storage::disk('public')->mimeType($file),
                "size" => Storage::disk('public')->size($file),
                "uploaded" => true,
                'link'  => $url
            );

            return response($info, 200);
        }   
    }   
    /**
    * 图片列表
    */
    public function ListImage()
    {
        $directory = 'image/'. User::find(Auth::user()->id)->phone . '/';
        $list = collect(Storage::disk('public')->allFiles('image/'))->map(function($item, $key) {
            return [
                'url' => Storage::disk('public')->url($item),
                'mtime' => Storage::disk('public')->lastModified($item),
            ];
        })->sortByDesc('mtime')->values()->all();

        return response(['list' => $list, 'state' => 'SUCCESS', 'start' => 0, 'total' => count($list)], 200);
    }
}
