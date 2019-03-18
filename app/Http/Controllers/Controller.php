<?php

namespace App\Http\Controllers;

use JWTAuth;
use Storage;
use App\User;
use App\Department;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $operator = array(
        ["label" => '等于', "value" => 0],
        ["label" => '不等于',"value" => 1],
        ["label" => '大于',"value" => 2],
        ["label" => '大于等于',"value" => 3],
        ["label" => '小于',"value" => 4],
        ["label" => '小于等于',"value" => 5],
        ["label" => '为空',"value" => 6],
        ["label" => '不为空',"value" => 7],
        ["label" => '包含', "value"   => 8],
        ["label" => "不包含", "value" => 9],
        ["label" => "全部", "value" => 10]
    );
    //数字类型的操作选项
    protected  $operatorNumber = array(
        ["label" => '等于', "value" => 0],
        ["label" => '不等于',"value" => 1],
        ["label" => '大于',"value" => 2],
        ["label" => '大于等于',"value" => 3],
        ["label" => '小于',"value" => 4],
        ["label" => '小于等于',"value" => 5],
    );
    //字符串类型的操作选项
    protected $operatorString = array(
        ["label" => '等于', "value" => 0],
        ["label" => '包含', "value"   => 8],
        ["label" => "不包含", "value" => 9]
    );
    //枚举类型的选项
    protected $operatorEnumeration = array(
        ["label" => '等于', "value" => 0],
        ["label" => '不等于',"value" => 1]
    );
    protected $operatorMap = array(
        '=', '<>','>','>=','<','<=', "is null", "is not null", "like", "not like"
    );

    protected $logic = array(['label' => "并且", 'value' => 1],['label' => '或者', 'value' => 2]);

    protected $logicMap = array('AND', 'OR');
    /**
    *过滤空格
	*@param Array $data
    *
    */
    protected function _TrimRequest(Array $data)
    {
    	if (is_array($data)) {
    		return array_map(array(__CLASS__,"_Trimap"), $data);
    	}

    	return $data;
    }
    protected function _Trimap($item)
    {
    	return trim($item);
    }

    /**
     * 获取当前user
     * @return $user
     */
    protected  function getUser()
    {
        $user = JWTAuth::parseToken()->authenticate()->getAttributes();
        $obj = new \stdClass();

        foreach ($user as $k => $v) {
            $obj->$k = $v;
        }

        return $obj;
    }

    /**
     * 获取当前用户id
     * @return $user_id
     */
    protected  function getUserId()
    {
        return $this->getUser()->id;
    }
    //判断用户是否admin
    protected function isAdmin()
    {
        return (bool) User::find($this->getUserId())->group == 'admin';
    }
    //判断当前用户有没有管理权限
    protected function isManager()
    {
        if (Department::where(['user_id' => $this->getUserId()])->first()) {
            return true;
        }
        return false;
    }
    //判断一个用户是不是admin
    protected function UserIsAdmin($userid)
    {
        return (bool) User::find($userid)->group == 'admin';
    }

    /**
     * 1、部门经理、助理 返回当前部门下所有用户的id
     */
    public function AuthIdList()
    {
        $user = User::find($this->getUserId());
        //获取用户的角色
        $userRoleName = User::find($user->id)->role->pluck('name');

        if ($userRoleName->contains("超级管理员")) {
            return User::all()->pluck('id');
        }
        else  {
            return User::where(['department_id' => $user->department_id])->get()->pluck('id');
        }
    }
    /**
    * 获取当前部门id
     *
     */
    protected  function getDepartId()
    {
        return Department::find(User::find($this->getUserId())->department_id)->id;
    }
    /**当前部门名称**/
    protected function getDepartName() {
        return Department::find(User::find($this->getUserId())->department_id)->name;
    }
    /**处理缩略图 jpg
     * @param $url 图像地址
     * @param $tw 缩略图宽度
     * @param $th 缩略图高度
     */
    protected function thumbImg($url, $tw, $th)
    {
        $info = getimagesize($url);
        switch ($info['mime']) {
            case 'image/jpeg':
                $oldImg = imagecreatefromjpeg($url);
                break;
            case 'image/png':
                $oldImg = imagecreatefrompng($url);
                break;
            case 'image/bmp':
                $oldImg = imagecreatefrombmp($url);
                break;
            case 'image/gif':
                $oldImg = imagecreatefromgif($url);
                break;
            default:
                return false;
        }
        //构建原图像

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

        return Storage::disk('public')->url($filepath);
    }

    /**
     *@CURL 工具
     * @param $type 请求类型
     * @param $url  url
     * @param $params 请求参数列表
     */
    public function CURL( string $url ,string $params = "", string $type = "GET", array $header = [], string $cookie = "")
    {
        $ch = curl_init();
        //CURL 参数设置
        curl_setopt($ch,CURLOPT_URL, $url);

        if (strtoupper($type) == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);

            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        }

        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }

        if ($result = curl_exec($ch)) {
            return $result;
        }

        return false;
    }
}
