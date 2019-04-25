<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerTag extends Model
{
    protected $fillable = [
    	'id',
    	'cust_id',
    	'machine',
		'num',
		'remark',
    	'addtime'
    ];

}
