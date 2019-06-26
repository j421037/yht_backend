<?php

namespace App\Http\Resources;

use App\ProductCategory;
use App\ProductsManager;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceTrackResouce extends JsonResource
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
            "id"            => $this->id,
            "date"          => date("Y-m-d H:i:s",$this->date),
            "remark"        => $this->remark,
            "category_name" => ProductCategory::find($this->category)->name,
            "brand_name"    => ProductsManager::find($this->product_brand)->brand_name,
            "data"          => [
                [
                    "freight" => $this->freight,
                    "remark"  => $this->remark,
                    "operate"       => $this->operate,
                    "change_val"    => $this->change_val,
                ]
            ]
        ];
    }
}
