<?php

namespace App\Http\Controllers;

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
}
