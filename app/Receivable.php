<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receivable extends Model
{
    use SoftDeletes;
    protected $fillable = [
    	'rid',
    	'amountfor',
    	'is_init',
    	'date',
    ];
}
