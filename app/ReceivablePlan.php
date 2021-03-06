<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceivablePlan extends Model
{
    use SoftDeletes;
    protected $fillable = ['content','rid','user_id','date','week'];
}
