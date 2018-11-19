<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    //
    protected $fillable = [
    	'id','pid','cust_id','refund','date','remark'
    ];
}
