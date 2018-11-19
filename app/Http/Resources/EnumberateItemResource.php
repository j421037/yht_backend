<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EnumberateItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'        => $this->id,
            'label'     => $this->name,
            'value'     => $this->value, 
            'index'     => $this->index,
            'disable'   => $this->disable,
            'eid'       => $this->eid
        ];
    }
}
