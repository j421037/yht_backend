<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\QueryException;
use  App\Exceptions\ApiException;
use App\Http\Requests\ARSetFieldStoreRequest;

class ARSetController extends Controller
{
    //
    const FieldType = array(
    	["label" => "数字", "value" => "int"],
    	["label" => "文本", "value" => "string"],
    	["label" => "金额", "value" => "decimal"],
    	["label" => "TEXT", "value" => "text"]
    );

    public function FieldType() 
    {
    	return response(self::FieldType, 200);
    }

    //保存辅助字段
    public function StoreField(ARSetFieldStoreRequest $Request)
    {

    	if ( !Schema::hasColumn('real_customer_assist_tables', $Request->name)) {
    		try {

	    		Schema::table('real_customer_assist_tables', function(Blueprint $table) use ($Request) {
	    			switch($Request->type) {
	    				case 'int':
	    					$table->integer($Request->name)->nullable();
	    					break;
	    				case 'string':
	    					$table->string($Request->name)->nullable();
	    					$action = "string('{$Request->name}', 23, 3)";
	    					break;
	    				case "decimal": 
	    					$table->decimal($Request->name, 23,3)->nullable();
	    					break;
	    				case "text":
	    					$table->text($Request->name)->nullable();
	    					break;
	    				default:
	    					throw new ApiException("非法的数据类型");		
	    			}
	    			
	    			
	    		});

	    		return response(['status' => 'success'], 200);

	    	} catch (QueryException $e) {
	    		return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
	    	} catch (ApiException $e) {
	    		return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
	    	}
    	} else {
    		return response(['status' => 'error', 'errmsg' => '字段已经存在'], 200);
    	}
    }
}
