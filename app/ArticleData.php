<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleData extends Model
{
    use SoftDeletes;
    protected $fillable= [
    	'article_id',
    	'clicks',
    	'comments',
    	'last_comment_user_id'
    ];
}
