<?php

namespace App\Http\Controllers;

use App\ProductsManager;
use Illuminate\Http\Request;
use App\Http\Requests\ProductPriceListRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\DatabaseManager;

class ProductPriceController extends Controller
{
    /**
     * productManager model
     */
    private $manager;
    private $db;
    /**
     * current price table name
     */
    private $table;

    public function __construct(ProductsManager $model, DatabaseManager $db)
    {
        $this->manager = $model;
        $this->db = $db;
    }

    /**
     *brand price table list
     *
     * @param $id
     *
     * @return response json
     */
    public function PriceList(ProductPriceListRequest $request)
    {
        $row = $this->manager->find($request->id);

        return response(["status" => "success",
                        "data" => [
                            "id" => $row->id,
                            "column" => $this->getColumn($row->columns),
                            "rows" => $this->getPriceData($row->table, $row->method,$this->groupField($row->columns))
                            ]
                        ]);
    }

    /**
     * price table column
     *
     * @param $json
     *
     * @return array;
     */
    private function getColumn($json) :array
    {
        $column = $this->groupField($json);
        array_push($column,["label" => "单位","value" => "unit"]);
        array_push($column,["label" => "价格","value" => "price"]);

        return $column;
    }

    private function groupField($json) :array
    {
        $column = [];
        $arr = json_decode($json);

        foreach ($arr as $v)
        {
            array_push($column,["label" => $v->description,"value" => $v->field]);
        }

        return $column;
    }

    /**
     * price table data
     *
     * @param $table table name
     *
     * @return $data rows
     */
    private function getPriceData(string $table,int $method, array $groupFields) :array
    {
        $field = collect($groupFields)->pluck('value')->toArray();
        $sql = "SELECT * FROM (SELECT * FROM {$table} ORDER BY `created_at` DESC LIMIT 0,99999999999) AS T0 GROUP BY ".implode(",",$field);
        $data = $this->db->select($sql);

        foreach ($data as $v)
        {
            if ($method == 0)
                $v->unit = "元/条";
            else
                $v->unit = "吨/条";
        }

        return (array) $data;
    }
}
