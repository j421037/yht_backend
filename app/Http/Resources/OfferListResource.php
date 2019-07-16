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
            "id"            => $this->id,
            "customer"      => $this->customer,
            "creator"       => $this->creator,
            "serviceor"     => $this->serviceor,
            "manager"       => $this->getBrand($this->product_brand_id),
            "date"          => $this->created_at->format("Y-m-d"),
            "formula_id"    => $this->formula_id,
            "formula"       => $this->formula->label,
        ];
    }

    private function getBrand($id): Array
    {
        $manager = ProductsManager::find($id);

        return ["tableId" => $manager->id,"brand" => $manager->brand_name,"method" => ["label" => $manager->method == 0 ? "面价打折":"吨价下浮","value" => $manager->method]];
    }

}
