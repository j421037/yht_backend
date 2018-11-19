<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\User;
use App\Role;

class AuthorityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $name = $request->route()->getAction();

        $controller = explode('@',substr($name['controller'],strrpos($name['controller'],'\\') + 1));

        $controller = strtolower($controller[0]);

        // $id = Auth::user()->id;

        $user = User::find(Auth::user()->id);

        if ( $user->group == 'admin') {

            return $next($request);
        }

        // $roleId = User::find($id)->role()->pluck('id');

        $roleId = $user->role()->pluck('id');

        $role = Role::whereIn('id', $roleId)->with(['permission'])->get();

        $role = $role->pluck('permission');
        // $role = $role->pluck('backend_path');
        $data = array();

        foreach ($role as $k => $v) {
            array_push($data, $v->pluck('backend_path'));
        }

        //collect 产生一个集合  collapse把多维数组组合成一位数组

        $data = array_filter(collect($data)->collapse()->toArray());

        $list = $data;

        $newList = array();

        foreach ($list as $k => $v) {

            if (strpos($v,'|') !== false) {

                $sub = explode('|', $v);

                if (is_array($sub) && count($sub) > 0) {

                    $newList = array_merge($newList, $sub);
                  
                }
            } else {
               array_push($newList, $v);
            }
        }

        $data = array_unique(array_filter($newList));
        
        $result = array_map(function($item) {
    
            return strtolower(trim($item));
           
        }, $data);

        if (!in_array(substr($controller, 0, strpos($controller, 'controller')), $result)) {

            return response(['msg' => '没有权限访问该功能', 'controller' => $controller, 'data' => $result], 403);
        }

        
        return $next($request);
    }
}
