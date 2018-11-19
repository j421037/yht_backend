<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ARType extends Model
{
    use SoftDeletes;
    protected $fillable = [
    	'name',
    	'key'
    ];
}
