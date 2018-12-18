<?php

namespace App\Http\Controllers;

use JWTAuth;
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
//        else {
//            return collect($user->id);
//        }
    }
}
