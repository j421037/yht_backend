<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticlePhoto extends Model
{
    protected $fillable= ['user_id','title','content','department_id'];
}
