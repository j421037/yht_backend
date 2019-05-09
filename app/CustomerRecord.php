<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerRecord extends Model
{
    protected $fillable = [
    	'id',
    	'cust_id',
    	'groups',
		'num',
		'remark',
    	'addtime',
        "user_id"
    ];

}
