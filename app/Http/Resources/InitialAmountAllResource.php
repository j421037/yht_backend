<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InitialAmountAllResource extends JsonResource
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
            "amountfor" => $this->amountfor,
            "date"      => date("Y-m-d",$this->date),
            "remark"    => $this->remark,
            "rid"       => $this->rid,
            "type"      => $this->type
        ];
    }
}
