<?php
/**
* RBAC  角色功能控制类
*/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Permission;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleNodeResource;
use App\Role;

class RoleController extends Controller
{
	/**
	*获取所有角色
	*/
	public function list()
	{
		$list = Role::select('id','name','description as desc','created_at as date')->get();

        return response(['data' => $list], 200);
	}


    /**
	*创建一个角色
    **/
	public function add(Request $request)
	{
		$data = $this->validator($request->post())->validate();
		$role = new Role;
		$role->name = $data['name'];
		$role->display_name = $data['name'];
		
        if (isset($data['desc'])) {
            $role->description = $data['desc'];
        }

		$role->save();

		if ($role->id > 0) {
			return response(['status' => 'success'], 200);
		} else {
			return response(['status' => 'error'], 200);
		}
	}

    /**
    * 更新一个角色
    */
    public function updateUser(Request $request) 
    {
        $id = $request->post('id');
        $data = $this->validator($request->post())->validate();
        $user = Role::find($id);

        $user->name = $data['name'];

        if (isset($data['desc'])) {
            $user->description = $data['desc'];
        }

        if ($user->save()) {
            return response(['status' => true], 200);
        }

        return response(['status' => false], 200);
    }

    /**
    * 获取单一角色
    * @param $id
    * @return json 
    */
    public function getOne(Request $request)
    {
        $id = $request->post('id');
        $list = Role::select('id', 'name','description as desc')->find($id);

        return response(['data' => $list], 200);
    }

    /**
    * 所有权限节点
    *@return json
    */
    public function getNode()
    {
        $list = Permission::with(['children'])->where(['pid' => 0])->get();

        $data = [];

        foreach ($list as $k => $v) {
            $std = new \stdClass;

            $std->id = $v->id;
            $std->label = $v->name;

            if ($v->children) {

                $children = [];

                foreach ($v->children as $kk => $vv) {
                    $children[$kk] = [];
                    $children[$kk]['id'] = $vv->id;
                    $children[$kk]['label'] = $vv->name;
                }

                $std->children = $children;
            }

            array_push($data, $std);
        }

        // return $data;
        return response(['data' => $data], 200);
    }

    /**
    * 当前角色分配的权限节点
    * @return json
    */
    public function getNodeSelect(Request $request)
    {
        $id = $request->post('id');

        $list = Role::with('permission')->where(['id' => $id])->first();

        $IDs = array();

        foreach ($list->permission as $k => $v) {
            $IDs[] = $v->id;
        }
        // $list = Role::select('pid as node')->where(['id' => $id])->first();
        // $data = explode(',',$list['node']);

        return response(['data' => $IDs], 200);
    }

    /**
    *设置当前角色的权限
    * @return json
    */
    public function setRole(Request $request) 
    {
        $id = $request->post('id');
        $list = $request->post('list');

        $parent = [];

        foreach ($list as $k => $v) {

            if (Empty($v)) {
                unset($list[$k]);
            }

            $pid = Permission::select('pid')->find($v);

            if ($pid->pid > 0) {
                //过滤顶层导航
                $parent[] = $pid->pid;
            }
        }

        

        $list = array_unique(array_merge($list, $parent));

        try {

            // $result = Role::where(['id' => $id])->update(['pid' => $list]);
           
            $role = Role::find($id);

            $result = $role->permission()->sync($list);
            
            if ($result) {
                return response(['status' => 'success'], 200);
            }

            return response(['status' => 'fail'], 200);

        } catch( Exception $e) {

            return response(['status' => 'fail', 'msg' => $e->getMessage()], 200);
        }
    }

	protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'desc' => '' 
        ]);
    }
}
