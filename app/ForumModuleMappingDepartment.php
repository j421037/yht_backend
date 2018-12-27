<?php
/**
 * 部门和自定义模块之间的映射
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumModuleMappingDepartment extends Model
{
    protected $fillable = ['name','sid','model','attr','index'];
}
