<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yadakhov\InsertOnDuplicateKey;

class RealCustomer extends Model
{
    //
    use SoftDeletes;
    use InsertOnDuplicateKey;

    protected $fillable = [
    	'name',
    	'user_id',
        'status',
        'pid',
    	'work_scope',
        'project_type',
		'attached',
    	'tags',
        'contract',
		'account_period',
    	'tax',
        'coop',
    	'track',
        'type',
		'phone',
		'level'
    	
    ];

    public function project()
    {
    	return $this->hasMany('App\Project','cust_id',"id");
    			
    }

    public function user()
    {
    	//return $this->hasOne('App\User','id')->select('name as user_name');
    	return $this->join('users', function($join) {
	            	$join->on('real_customers.user_id', '=', 'users.id');
	        	})->select('real_customers.*', 'users.name as user_name');
    }
}
