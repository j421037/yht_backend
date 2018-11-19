<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticleAnswer extends Model
{
   	protected $fillable= [
    	'article_id',
    	'user_id',
    	'content',
    ];
}
