<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerTrack extends Model
{
    protected $fillable = [
    	'id',
    	'cust_id',
    	'content',
    	'addtime'
    ];

}
