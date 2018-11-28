<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilterProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id','name','module', 'conf','user_id'
    ];
}
