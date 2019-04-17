<?php

namespace App\Http\Resources;

use App\ProductsManager;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id"        => $this->id,
            "customer"  => $this->customer,
            "creator"   => $this->creator,
            "serviceor" => $this->serviceor,
            "brand"     => $this->getBrand($this->product_brand_id),
            "date"      => $this->created_at->format("Y-m-d"),
            "opval"     => $this->operate_val,

        ];
    }

    public function getBrand($id)
    {
        return ProductsManager::find($id)->brand_name;
    }

}
