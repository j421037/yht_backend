<?php
/**
 * Created by PhpStorm.
 * @author: 老王
 * Date: 2018/11/20
 * Time: 14:21
 * 应收用户操作权限判断
 *
 */
namespace App\Http\Controllers\Traits;

use App\Role;

trait  ARSumRole {

    private $roles;

    /**用户所拥有的权限
     * @param $user_id 用户id
     * @return Eloquent
     */
    public function getRole($user_id)
    {
        $roles = $this->user->find($user_id)->role;

        return $roles;
    }

    /**
     * 验证权限
     * @param $roles Eloquent
     * @param  boolean
     */
    public function checkRole($roles, Role $role)
    {
        $list = $role->whereIn('id', $roles->pluck('id'))->with(['permissionAll'])->get();
        $className = get_class($this);
        $backend = [];
        //获取当前类名
        $selfName = strtolower(substr($className,strrpos($className, "\\") + 1));
        $selfName = substr($selfName,0,strpos($selfName,'controller'));
        $json = response()->json($list)->getOriginalContent();
        $list = json_decode($json, true);

        //提取控制器名称
        foreach ($list as $k => $v) {
            $collect = collect($v['permission_all']);
            array_push($backend, $collect->pluck("backend_path"));
        }

        $collapse = collect($backend)->collapse()->all();

        foreach ($collapse as $k => $v) {
            $item = strtolower($v);

            //转换成小写后对比
            if (strpos($item, $selfName) !== false) {
                return true;
                break;
            }

        }

        return false;
    }
}
