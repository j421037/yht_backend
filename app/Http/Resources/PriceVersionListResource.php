<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceVersionListResource extends JsonResource
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
            "label" => $this->version." (日期: ".$this->created_at." 运费：".$this->freight.")",
            "value" => $this->id,
            "freight"   => $this->freight
        ];
    }
}
