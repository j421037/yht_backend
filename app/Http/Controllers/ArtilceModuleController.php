<?php
/**
 * 论坛各模块下的文章处理
 * 1、当前模块下的用户 可以查看当前部门的所有文章
 * 2、非当前模块下的用户 可以查看当前部门公开的文章
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArtilceModuleController extends Controller
{
    /**
     * 返回文章列表
     */
    public function index(Request $request)
    {

    }
}
