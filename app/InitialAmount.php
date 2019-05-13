<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InitialAmount extends Model
{
    //
    protected $fillable = ["rid","amountfor","type","date","remark"];
}
