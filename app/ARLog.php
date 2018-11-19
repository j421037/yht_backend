<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ARLog extends Model
{
    protected $fillable = [
    	'user_id',
    	'fid',
    	'type',
    	'model',
    	'old_value',
    	'new_value'
    ];
}
