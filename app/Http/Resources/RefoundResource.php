<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefoundResource extends JsonResource
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
            'id'        => $this->id,
            'refund'    => $this->refund,
            'date'      => date('Y-m-d',$this->date),
            'remark'    => $this->remark,
            'created'   => $this->created_at
        ];
    }
}
