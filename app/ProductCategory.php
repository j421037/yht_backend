<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    //
    protected $fillable = ["name","creator","abbr"];

    public function childrens()
    {
        return $this->hasMany("App\ProductsManager","category_id","id");
    }
}
