<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;//软删除

class Customer extends Model
{
    use SoftDeletes;

	//把日期更新的格式改为时间戳
    protected $dateFormat = 'U'; 

    protected $fillable = [
            'name'         ,
            'user_id'       ,
            'phone'         ,
            'demand'       ,
            'wechat'		,
            'qq'			,
            'brand_id'        ,
            'project_name' ,
            'description'   ,
            'province'     ,
            'city'         ,
            'area',
            'publish',
            'department_id',
            'create_user_id',
            "real_customer_id",
            "real_project_id"
        ];

    public function brand()
    {
        return $this->belongsTo('App\Brand');
    }

    public function note()
    {
        return $this->hasMany("App\CustomerNote","customer_id","id");
    }
}
