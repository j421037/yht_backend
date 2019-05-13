<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableListResource extends JsonResource
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
            'id'                => $this->id,
            'amountfor'         => $this->amountfor,
            'amountfor_format'  => number_format($this->amountfor),
            'date'              => date('Y-m-d',$this->date),
            'remark'            => $this->remark,
            'type'              => $this->type,
            "type_name"         => $this->type == 0 ? "终端" : "同行"
        ];
    }
}
