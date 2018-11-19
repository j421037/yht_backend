<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
	use SoftDeletes;
    //$fillable  白名单  可以修改的值
    protected $dates = ['deleted_at'];
    protected $fillable = ['title', 'body', 'status', 'top', 'user_id','category_id', 'titlepic'];

    public function isFine() 
    {
    	return $this->hasOne('App\ArticleData');
    }
}
