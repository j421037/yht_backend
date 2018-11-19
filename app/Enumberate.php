<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enumberate extends Model
{
	use SoftDeletes;
   	protected $fillable = ['name', 'user_id'];

   	public function item()
   	{
   		return $this->hasMany('App\EnumberateItem', 'eid', 'id')->orderBy("index", "asc");
   	}
}
