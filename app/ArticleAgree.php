<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleAgree extends Model
{
	use SoftDeletes;
	protected $fillable = ['article_id', 'create_user_id', 'agree_user_id'];

	public function user()
	{
		// return $this->hasOne('App\Department','user_id', 'agree_user_id');
		return $this->join('departments','user_id', '=','agree_user_id');
	}
}
