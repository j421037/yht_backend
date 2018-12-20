<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumModule extends Model
{
    use SoftDeletes;
    protected $fillable = ['module_name','disable'];
}
