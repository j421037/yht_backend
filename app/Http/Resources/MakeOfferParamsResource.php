<?php

namespace App\Http\Resources;

use App\MakeOfferFormula;
use App\PriceVersion;
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
            array_push($rows, [
                            "label"     => $v->brand_name,
                            "value"     => $v->id,
                            "products"  => $v->products,
                            "field_map" => $v->field_map,
                            "mode"      => $v->method,
                            "versions"  => $this->getVersions($v->id),
                            "formula_param"   => $v->formula_param,
                            "formulas"  => $this->getFormula($v->id)
                    ]);
        }

        return $rows;
    }

    /**
     * 所有的调价版本
     */
    private function getVersions($id) : array
    {
        $rows = PriceVersion::where(["product_brand" => $id])->orderBy("id","desc")->get();
        $data = [];

        foreach ($rows as $row)
        {
            array_push($data,["label" => $row->version." (".date("Y-m-d",$row->date).") ","value" => $row->id]);
        }

        return $data;
    }

    private function getFormula($fid) : array
    {
        if (!$fid)
            return [];

        $result = [];
        $formulas = MakeOfferFormula::where(["table_id" => $fid])->get();

        foreach ($formulas as $formula) {
            array_push($result, ["label" => $formula->formula, "value" => $formula->id]);
        }

        return $result;
    }
}
