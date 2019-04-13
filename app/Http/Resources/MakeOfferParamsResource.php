<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MakeOfferParamsResource extends JsonResource
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
            "label"     => $this->name,
            "value"     => $this->id,
            "childrens" => $this->child($this->childrens)
        ];
    }

    /**
     * brands list
     * @params $arr Object
     * @return array
     **/
    private function child($arr) :array
    {
        $rows = [];

        foreach($arr as $v)
        {
            array_push($rows, ["label" => $v->brand_name, "value" => $v->brand_id,"products" => $v->products,"field_map" => $v->field_map]);
        }

        return $rows;
    }

//    /**
//     * prices table field
//     */
//    private function
}
