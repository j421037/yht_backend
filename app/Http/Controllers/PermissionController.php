<?php

namespace App\Http\Controllers;

use Route;
use Auth;
use Storage;
use Config;
use App\User;
use Illuminate\Http\Request;
use App\Permission;
use App\Http\Resources\PermissionResource;
use App\Http\Requests\PermissionFormRequest;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
   
    /**
    *添加功能组
    */
    public function addGroup(PermissionFormRequest $request)
    {

    	try {

            $data = $request->post();

            if (!Empty($request->component_pc)) {

                if ($file = $this->__SaveVueCom($request->component_pc)) {

                    $data['template_pc'] = $file;
                    $data['template_pc_name'] = $request->component_pc->getClientOriginalName();

                } else {

                    throw new \Exception($request->component_pc->getClientOriginalName()."不是标准的vue组件");
                    
                }
            }

            if (!Empty($request->component_mobile)) {

                if ($file = $this->__SaveVueCom($request->component_mobile)) {

                    $data['template_mobile'] = $file;
                    $data['template_mobile_name'] = $request->component_mobile->getClientOriginalName();

                } else {

                    throw new \Exception($request->component_mobile->getClientOriginalName()."不是标准的vue组件");
                    
                }
            }

    		if ($result = Permission::create($data)) {

	    		return response(['status' => 'success', 'data' => $result], 200);

	    	} else {

	    		return response(['status' => 'error', 'error'=>'功能添加失败'], 200);
	    	}

    	} catch(\Exception $e) {
    		
    		return response(['status' => 'error',  'error' => $e->getMessage()], 200);
    	} 
    }
    
    /**
    * 更新功能
    */
    public function update(PermissionFormRequest $request) 
    {

        try {

            $data = $request->post();

            foreach ($data as $k => $v) {

                if ($v == 'null') {
                    $data[$k] = null;
                }
            }

            unset($data['node_type']);

            if (!Empty($request->component_pc)) {

                if ($file = $this->__SaveVueCom($request->component_pc)) {

                    $data['template_pc'] = $file;
                    $data['template_pc_name'] = $request->component_pc->getClientOriginalName();

                } else {

                    throw new \Exception($request->component_pc->getClientOriginalName()."不是标准的vue组件");
                    
                }
            } else {
                unset($data['template_pc']);
                unset($data['template_pc_name']);
            }

            if (!Empty($request->component_mobile)) {

                if ($file = $this->__SaveVueCom($request->component_mobile)) {

                    $data['template_mobile'] = $file;
                    $data['template_mobile_name'] = $request->component_mobile->getClientOriginalName();

                } else {

                    throw new \Exception($request->component_mobile->getClientOriginalName()."不是标准的vue组件");
                    
                }
            } else {
                unset($data['template_mobile']);
                unset($data['template_mobile_name']);
            }

            $result = Permission::find($request->id);

            $result = $result->update($data);

            if ( $result != false) {

                return response(['status' => 'success'], 200);
            }   

        } catch (\Exception $e) {

           return response(['status' => 'fail', 'error' => $e->getMessage()], 200);
        }

       

    }

    /**
    *获取功能组信息
    */
    public function getGroups()
    {
    	return Permission::where('node_type', 'group')->select(['id as value','name as label'])->get();
    }

    /**
    * 功能管理
    */
    public function getAll(Request $request)
    {

        $list = Permission::with(['children'])->where('pid', 0)->get();
        $data = array();
        $index = 0;
        foreach($list as $k => $v) {
            $v->parentNode = '顶层';

            $data[$index] = new PermissionResource($v);

            ++$index;

            if (count($v->children) > 0) {
                foreach($v->children as $kk => $vv) {
                    $vv->parentNode = $v->name;
                    $data[$index] = new PermissionResource($vv);
                    ++$index;
                }
            }
        }

        return response(['list' => $data], 200);
    }

    /**
    * 删除功能组
    */
    public function delete(Request $request) {
        $user = User::find(Auth::user()->id);

        if ($user->group != 'admin') {
            return response(['status' => 'error', 'msg' => '没有该操作的权限！'], 200);
        }

        try {

            $permission = Permission::find($request->id);
            //默认删除节点
            $list = [$permission->id];

            if ($permission->pid == 0) {
                //删除功能组
                $pluck = Permission::where(['pid' => $permission->id])->get()->pluck('id')->toArray(); 

                $list = array_merge($list, $pluck);
            } 

          
            if (Permission::destroy($list))  {
                return response(['status' => 'success'], 200);
            } 

        } catch (\Illuminate\Database\QueryException $e) {

            return response(['status' => 'error', 'msg' => $e->getMessage()], 200);
        }
    }

    /**
    * 验证并保存组件
    * @param upload file
    * @return $filename | false
    */
    private function __SaveVueCom($file)
    {
        $patt = "/<template>[\s\S]*<\/template>[\s\S]*<script[\s\S]*<\/script>/i";


        $file = Storage::disk('component')->putFile('', $file);

        $content = Storage::disk('component')->get($file);

        preg_match_all($patt, $content, $result);

        // var_dump($content);

        if (Empty($result[0])) {

            return false;
            
        }

        return $file;
    }

}
