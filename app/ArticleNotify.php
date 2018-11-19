<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleNotify extends Model
{
	use SoftDeletes;
   	protected $fillable = [
    	'article_id','sender', 'receiver','is_read','answer_id'
    ];
}
