<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Department;
use Illuminate\Http\Request;
use App\Http\Resources\DepartmentResource;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{

    /**
    *新建一个部门
    */
    public function store(Request $request)
    {
        if ($index = Department::max('index')) {
            $index += 1;
        }
        else {
            $index = 0;
        }

    	if (Department::create(['name' => $request->name, 'index' => $index])) {

    		return response(['status' => 'success'], 200);
    	}
    }

    /**
    * 返回部门
    */
    public function list()
    {
    	return response(['data'	=> DepartmentResource::collection(Department::all())], 200);
    }

    /**修改**/
    public function modify(Request $request) 
    {
    	$result = Department::where(['id' => $request->id])->update(['name' => $request->name]);    	

    	if ($result != false) {
    		return response(['status' => 'success'], 200);
    	}
    } 

    /**返回成员信息**/
    public function user(Request $request) 
    {
    	$user = User::where(['department_id' => $request->department_id])->orWhere(['department_id' => null])->select('name','id')->get();

    	$checked = Department::find($request->department_id)->users()->pluck('id');

    	return response(['data' => $user, 'checked' => $checked]);
    }

    /**
    *+------------------------------------
    *+ 修改成员
    *+---------------------------------
    *
    */
    public function updateUser(Request $request)
    {
    	//DB::enableQueryLog() 开启数据库日志
    	//DB::getQueryLog() 获取数据库日志

    	try {
    		if ($request->list) {

    			//模型保存
    			$result = Department::find($request->departmentId)->users()->saveMany(User::whereIn('id', $request->list)->get());

			$checked = Department::find($request->departmentId)->users()->pluck('id');  

			$list = $checked->diff($request->list)->values();  

			if (count($list) > 0) {

				$user = User::whereIn('id', $list)->get();

				foreach ($user as $v) {
					//belongsto删除关联 
			    		$v->department()->dissociate(Department::find($request->departmentId));

			    		$v->save();
			    	}
			}			

    		} else {

    			$result = User::where(['department_id' => $request->departmentId])->update(['department_id' => null]);
    		}

    		if ($result != false) {
	    		return response(['status' => 'success' ], 200);
	    	}
    	} catch(\Illuminate\Database\QueryException $e) {
    		
    		return response(['status' => 'fail', 'error' => $e->getMessage() ], 200);	
    	}
    	
    }

    /**
    *+----------------
    *+ 设置管理员
    *+---------------
    */
    public function manager( Request $request) 
    {
    	try {
    		$result = Department::find($request->departmentId)->update(['user_id' => $request->userId]);

    		if ($result != false) {
    			return response(['status' => 'success'], 200);
    		}

    	} catch(\Illuminate\Database\QueryException $e) {

    		return response(['status' => 'fail', 'error' => $e->getMessage() ], 200);	
    	}
    	
    }
}
