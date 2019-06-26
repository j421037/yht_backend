<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonTarget extends Model
{
    //
    protected $fillable = ["user_id","target", "year"];
}
