<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticleComment extends Model
{
   	protected $fillable= [
    	'article_id',
    	'user_id',
    	'comment',
    	'to_user_id',
    	'answer_id'
    ];
}
