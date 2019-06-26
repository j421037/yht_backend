<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PriceVersion extends Model
{
    //
    protected $fillable = ["category","product_brand","date","version","atta_id","remark","operate", "change_val","freight"];
}
