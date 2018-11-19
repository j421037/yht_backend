<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerPost extends Model
{
    //把日期更新的格式改为时间戳
    protected $dateFormat = 'U';

    protected $fillable = [
    	'user_id',
    	'customer_id',
    	'content',
    	'date'
    ];
}
