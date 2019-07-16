<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeneralOffer extends Model
{
    //
    protected $fillable = ["creator_id","creator","serviceor_id","serviceor","customer_id","customer","formula_id","product_brand_id","version_id","price_id"];

    public function formula()
    {
        return $this->hasOne("App\MakeOfferFormula", "id", "formula_id");
    }
}
