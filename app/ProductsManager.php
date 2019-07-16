<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductsManager extends Model
{
    //
    protected $fillable = ["category_id", "brand_id","brand_name","table", "method","columns"];

    public function spec()
    {
        return $this->hasMany("App\ProductSpec","table_id", "id");
    }
}
