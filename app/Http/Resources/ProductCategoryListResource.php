<?php

namespace App\Http\Resources;

use App\PriceVersion;
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
            $data["method"] = $v->method;
            $data["method_l"] = $v->method == 0 ? "面价打折" : "吨价下浮";
            $data["fields"] = $this->decodeDescription($v->columns);
            $data["notice"] = $this->notice($v->id);
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

    /**
     * 调价信息
     */
    private function notice($id) : string
    {
        $row = PriceVersion::where(["product_brand" => $id])->orderBy("id","desc")->first();

        if ($row)
        {
            return sprintf("当前价格版本: %s, 更新于: %s, 运费: %s元, 备注: %s", $row->version, date("Y-m-d H:i:s", $row->date), $row->freight, $row->remark);
        }

        return "暂无数据";
    }


}
