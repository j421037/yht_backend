<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PotentialCustomer extends Model
{
	use SoftDeletes;

    protected $fillable = ['name', 'user_id'];

    public function project()
    {
        return $this->hasMany("App\PotentialProject", "cust_id", "id");
    }
}
