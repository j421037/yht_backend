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
            "date"          => $this->created_at->format("Y-m-d H:i:s"),
            "remark"        => $this->remark,
            "category_name" => ProductCategory::find($this->category)->name,
            "brand_name"    => $this->brandName($this->product_brand),
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

    public function brandName($brand)
    {
        if ($brand) {
            $result = ProductsManager::find($this->product_brand);
            if ($result)
                return $result->brand_name;
        }

        return null;
    }
}
