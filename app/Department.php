<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $dateFormat = 'U';
    protected $fillable = [
    	'name',
    	'user_id'
    ];

    public function users()
    {
    	return $this->hasMany('App\User');
    }

    public function agree()
    {
    	return $this->hasMany('App\ArticleAgree', 'agree_user_id','user_id');
    }
    public function user()
    {
    	return $this->hasOne('App\User', 'id', 'user_id');
    }
}
