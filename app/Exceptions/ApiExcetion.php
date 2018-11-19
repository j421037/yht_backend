<?php
namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    function __construct($msg='')
    {
        parent::__construct($msg);
    }

    public function render($request, Exception $e)
    {
    	if ($e instanceof ApiException) {
    		$result = array(
    			"errmsg" => $e->getMessage(),
    		);

    		return response($result, 200);
    	}

    	return parent::render($request, $e);
    }
}