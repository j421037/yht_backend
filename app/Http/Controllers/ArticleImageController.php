<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Filesystem\FilesystemManager;

class ArticleImageController extends Controller
{
    protected $filesystem;

    public function __construct(FilesystemManager $FilesystemManager)
    {
        $this->filesystem = $FilesystemManager->disk('public');
    }

    //随机图片
    public function index(Request $request)
    {
        $url = $this->filesystem->url('thumb/default');
        $data = [];

        for ($i = 0; $i < 16; ++$i) {
            $index = mt_rand(1, 500);
            $file = $url.'/'.$index.'.jpg';
            array_push($data, $file);
        }

        return response($data, 200);
    }
    public function test(Request $request)
    {
        $url = "http://api.mtyqx.cn/tapi/random.php?t=";
        $data = [];
        $mh = curl_multi_init();
        for( $i = 0; $i< 16; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url.time().'&qq='.mt_rand(0,100));
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//            curl_exec($ch);
//            $info = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
//            curl_close($ch);
//            array_push($data, $info);
            curl_multi_add_handle($mh, $ch);
        }

        $running = null;

        do {
            curl_multi_exec($mh, $running);
            //阻塞一会等待有数据可读，返回可读数量，失败为-1，避免一直循环占用CPU
            if ($running) {
                curl_multi_select($mh, 5);
                //循环读取连接中的数据
                while($done = curl_multi_info_read($mh)) {
                    $res = curl_getinfo($done['handle'],CURLINFO_EFFECTIVE_URL);
                    array_push($data, $this->thumbImg($res,120, 120));
                    curl_multi_remove_handle($mh, $done['handle']);
                }
            }

        } while($running > 0);
//        var_dump($data);
        return $data;
    }

    /**处理缩略图 jpg
     * @param $url 图像地址
     * @param $tw 缩略图宽度
     * @param $th 缩略图高度
     */
    protected function thumbImg($url, $tw, $th)
    {
        //构建原图像
        $oldImg = imagecreatefromjpeg($url);
        //新建缩略图
        $thumb = ImageCreateTrueColor($tw, $th);
        if (!$oldImg) {
            return false;
        }

        $ox = imagesx($oldImg);
        $oy = imagesy($oldImg);
        //生成缩略图
        imagecopyresampled($thumb,$oldImg,0,0,0,0,$tw,$th,$ox,$oy);
        $diskpath = '/thumb/'.date('Y-m-d', time()).'/';
        $dir = storage_path('app/public'.$diskpath);
        $filename = md5($url.time()).'.jpg';

        if (!file_exists($dir)) {
            mkdir($dir,0777, true);
        }

        $path = $dir.$filename;
        $filepath = $diskpath.$filename;

        imageJpeg($thumb, $path);
        imagedestroy($thumb);
        imagedestroy($oldImg);

        return $this->filesystem->url($filepath);
    }
}
