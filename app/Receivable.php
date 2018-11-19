<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receivable extends Model
{
    use SoftDeletes;
    protected $fillable = [
    	'cust_id',
    	'amountfor',
    	'is_init',
    	'date',
    ];
}
