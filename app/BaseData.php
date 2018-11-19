<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseData extends Model
{
	protected $dateFormat = 'U'; //把日期更新的格式改为时间戳
    protected $fillable = [
    	'name','value', 'description','type'
    ];
}
