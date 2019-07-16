<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductManagerListResource extends JsonResource
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
            "name"      => $this->brand_name,
            "table"     => $this->table,
            "method_l"  => $this->mode == 0 ? "面价打折" : "吨价下浮",
            "method"    => $this->mode,
            "description"     => $this->buildField($this->columns),
            "field"     => json_decode($this->columns,true),
            "orderby"   => $this->orderby,
            "sort"      => $this->sort,
        ];
    }

    /**
     *分解字段
     * @param json
     * @return string
     */
    public function buildField($json) :string
    {
        $field = [];
        $arr = json_decode($json,true);

        foreach($arr as $v)
        {
            array_push($field, $v["description"]."(".$v["field"].")");
        }

        return implode("、",$field);
    }
}
