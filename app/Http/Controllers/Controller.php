<?php

namespace App\Http\Controllers;

use JWTAuth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


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
}
