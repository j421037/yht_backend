<?php

namespace App\Http\Resources;

use App\Http\Resources\EnumberateItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EnumberateResource extends JsonResource
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
            'id'    => $this->id,
            'name'  => $this->name,
            'item'  => EnumberateItemResource::collection($this->item)
        ];  
    }
}
