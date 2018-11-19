<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    //
    public function child()
    {
    	return $this->hasMany(static::class, 'parent_id', 'id');
    }
}
