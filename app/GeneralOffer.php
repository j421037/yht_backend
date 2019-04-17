<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeneralOffer extends Model
{
    //
    protected $fillable = ["creator_id","creator","serviceor_id","serviceor","customer_id","customer","operate","operate_val","product_brand_id","version_id","products"];
}
