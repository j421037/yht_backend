<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnumberateItem extends Model
{
	use SoftDeletes;
    protected $fillable = ['name','value', 'index','disable', 'eid', 'created_at', 'updated_at'];
}
