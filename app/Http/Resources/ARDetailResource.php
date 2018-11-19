<?php

namespace App\Http\Resources;

use App\ARType;
use Illuminate\Http\Resources\Json\JsonResource;

class ARDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id'        => $this->id,
            'amountfor' => $this->amountfor,
            'date'      => $this->date ? date('Y-m-d', $this->date) : null,
            'is_init'   => isset($this->is_init) ? $this->is_init : 0,
            'remark'    => $this->remark,
            'discount'  => isset($this->discount) ? $this->discount : null
        ];
    }
}
