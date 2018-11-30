<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PotentialProject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'cust_id',
        'user_id',
        'tid',
        'tag',
        'tax',
        'estimate',
        'phone_num',
    ];
}
