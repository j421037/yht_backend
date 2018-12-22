<?php

namespace Miao\Providers\Model;

use Illuminate\Database\Eloquent\Model;

class WxToken extends Model
{
    //
    protected $dateFormat = 'U'; //把日期更新的格式改为时间戳
    protected $fillable = array(
    	'token','expire'
    );
}
