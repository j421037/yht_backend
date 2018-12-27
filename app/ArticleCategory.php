<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    //
    protected $fillable = ['name', 'user_id','module_id'];

    public function department()
    {
        return $this->hasOne('App\ForumModuleMappingDepartment','id','module_id');
    }
}
