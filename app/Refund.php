<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Refund extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
    	'id','pid','cust_id','refund','date','remark'
    ];
}
