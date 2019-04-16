<?php

namespace App\Http\Controllers;

use App\ProductCategory;
use App\ProductsManager;
use App\PriceVersion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests\ProductPriceListRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\DatabaseManager;
use App\Http\Resources\MakeOfferParamsResource;
use App\Http\Requests\ProductPriceUpdateRequest;

class ProductPriceController extends Controller
{
    /**
     * productManager model
     */
    private $manager;
    private $category;
    private $db;
    private $priceVersion;
    /**
     * current price table name
     */
    private $table;

    public function __construct(ProductsManager $model, ProductCategory $category , DatabaseManager $db, PriceVersion $version)
    {
        $this->manager = $model;
        $this->category = $category;
        $this->db = $db;
        $this->priceVersion = $version;
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
     * makeoffer params
     * @return  [category:[table1,table2]]
     */
    public function MakeOfferParams(Request $request)
    {
        $data = $this->category->with(['childrens'])->get();
        $db = $this->db;
        $row = [];

        $data->map(function(&$items) use (&$db) {
            $items->childrens->map(function(&$item) use ($db) {
                $collect = collect(json_decode($item->columns,true));
                $fields = $collect->pluck("field")->toArray();
                $fieldMap = [];
                $collect->map(function($f) use (&$fieldMap) {
                    $fieldMap[$f["field"]] = $f["description"];
                });

                $item->products = $db->table($item->table)->select($fields)->groupBy($fields)->get();
                $item->field_map = $fieldMap;
            });
        });


        return response(["staus" => "success","data" => MakeOfferParamsResource::collection($data)], 200);
    }

    /**
     * change brand price
     *
     * @params $request []
     */
    public function update(ProductPriceUpdateRequest $request)
    {
        try {
            $fileCollect = $request->fileid;

            if (is_array($fileCollect) && count($fileCollect) > 0)
            {
                $fileCollect = collect($fileCollect)->pluck("id")->toArray();
                $fileCollect = implode(",",$fileCollect);
            }

            $this->db->beginTransaction();
            $model = $this->priceVersion->create([
                "category" => $request->category,
                "product_brand" => $request->brand,
                "date" => strtotime($request->date),
                "version" => $request->version_str,
                "atta_id" => $fileCollect
            ]);

            if ($model) {
                $manager = $this->manager->find($model->product_brand);
                $rows = $request->rows;

                foreach ($rows as &$row)
                {
                    $row["version"] = $model->id;
                    $row["created_at"] = time();
                    $row["version_l"] = $model->version;
                }

                $result = $this->db->table($manager->table)->insert($rows);

                if ($result)
                {
                    $this->db->commit();
                    return response(["status" => "success"], 201);
                }
            }
        }
        catch (QueryException $e)
        {
            $this->db->rollBack();
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
        return response($model);
    }

    /**
     * price versions
     * @params $product_brand
     * @return price_versions list
     */
    public function PriceVersionList(Request $request)
    {
        $data = $this->priceVersion->where(["product_brand" => $request->product_brand]);
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
            else if ($method == 1)
                $v->unit = "吨/条";
        }

        return (array) $data;
    }
}
