<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CustomerNote extends Model
{
    //把日期更新的格式改为时间戳
    protected $dateFormat = 'U';
    protected $fillable = [
    	'user_id',
    	'customer_id',
    	'action'
    ];

    
   
}
