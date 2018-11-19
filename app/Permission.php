<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;
    //protected $dateFormat = 'U'; //把日期更新的格式改为时间戳
    protected $dates = ['deleted_at'];
    /**关联表名**/
    protected $table = 'permissions';
    /**白名单字段**/
    protected $fillable = array(
    	'name',
        'pid',
        'front_path',
        'backend_path',
        'node_type',
        'classname', 
        'description',
        'show_pc',
        'show_mobile',
        'template_pc',
        'template_mobile',
        'mobile_name',
        'mobile_classname',
        'mobile_path',
        'template_mobile_name',
        'template_pc_name',
        'mobile_sort',
        'pc_sort',
    );

    public function parent()
    {
	    return $this->belongsTo(static::class, "id", "pid");
	}

	public function children()
    {
	    return $this->hasMany(static::class, "pid", "id");
	}

    public function BindAttr()
    {
        return $this->hasMany('App\BindAttr', 'pid', 'id');
    }
}
