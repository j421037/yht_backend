<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerFree extends Model
{
    //
    protected $fillable = [
    	'customer_id',
    	'user_id',
    	'reason'
    ];
}
