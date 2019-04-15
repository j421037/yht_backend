<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryListResource extends JsonResource
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
            "id"   => $this->id,
            "name" => $this->name,
            "abbr" => $this->abbr,
            "children" => $this->children($this->childrens)
        ];
    }

    private function children($child) :array
    {
        $rows = [];

        foreach ($child as $v)
        {
            $data = [];
            $data["id"]     = $v->id;
            $data["name"]   = $v->brand_name;
            $data["table"]  = $v->table;
            $data["method"] = $this->method;
            $data["method_l"] = $v->method == 0 ? "面价打折" : "吨价下浮";
            $data["fields"] = $this->decodeDescription($v->columns);
            array_push($rows, $data);
        }

        return $rows;
    }

    private function decodeDescription($json) :array
    {
        $array = json_decode($json,true);
        $rows = [];
        $fields = [];

        array_walk($array, function($item) use (&$rows, &$fields) {
            array_push($rows,$item["field"]."(".$item["description"].")");
            array_push($fields, ["key" => $item["field"],"value" => $item["description"]]);
        });

        return ["description" => implode("、",$rows), "mapping" => $fields];
    }
}
