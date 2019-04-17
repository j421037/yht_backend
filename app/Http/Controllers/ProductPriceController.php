<?php

namespace App\Http\Controllers;

use App\ProductCategory;
use App\ProductsManager;
use App\PriceVersion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests\ProductPriceListRequest;
use Illuminate\Database\DatabaseManager;
use App\Http\Requests\PriceVersionListRequest;
use App\Http\Requests\FastUpdateRequest;
use App\Http\Resources\PriceVersionListResource;
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
     * change brand price
     *
     * @params $request []
     */
    public function update(ProductPriceUpdateRequest $request)
    {
        $version = new \StdClass;
        $fileCollect = $request->fileid;

        if (is_array($fileCollect) && count($fileCollect) > 0)
        {
            $fileCollect = collect($fileCollect)->pluck("id")->toArray();
            $fileCollect = implode(",",$fileCollect);
        }
        else {
            $fileCollect = "";
        }

        $version->category = $request->category;
        $version->product_brand = $request->brand;
        $version->version = $request->version_str;
        $version->date = strtotime($request->date);
        $version->remark = $request->remark;
        $version->atta_id = $fileCollect;

        return response($this->BatchUpdate((Array)$version,$request->rows), 200);
    }

    /**
     * fast update
     *
     * @param  $request->operate == 1 up  == 0 down
     **/
    public function FastUpdate(FastUpdateRequest $request)
    {

        //find table name from id
        $manager = $this->manager->find($request->product_brand);
        $version = $this->priceVersion->find($request->version_id);
        $rows = $this->db->table($manager->table)->where(["version" => $request->version_id])->get();
        $version->version = $request->new_version;
        $version->change_val = $request->discount;
        $version->remark = $request->remark;
        $version->operate = $request->operate;

        if ($manager->method == 0)
        {
            $methodName = "bcmul";
            if ($request->operate == 1)
            {
                //up
                $operateNumber = (100 + $request->discount) / 100;
            }
            else {
                //down
                $operateNumber = (100 - $request->discount) / 100;
            }
        }
        else {
            $methodName = "bcadd";
            if ($request->operate == 1)
            {
                //up
                $operateNumber = $request->discount;
            }
            else {
                //down
                $operateNumber = $request->discount * -1;
            }
        }

        $rows = $rows->map(function($item) use ($methodName,$operateNumber) {
                    $item->price = $methodName($item->price,$operateNumber,2);
                    unset($item->id);
                    return (Array)$item;
                });

        return response($this->BatchUpdate($version->toArray(),$rows->all()), 200);
    }


    /**
     * price versions
     * @params $product_brand
     * @return price_versions list
     */
    public function PriceVersionList(PriceVersionListRequest $request)
    {
        $data = $this->priceVersion->where(["product_brand" => $request->product_brand])->orderBy("id","desc")->get();
        return response(["status" => "success", "data" => PriceVersionListResource::collection($data)], 200);
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
                $v->unit = "元/吨";
        }

        return (array) $data;
    }

    /**
     * @params  $version [category, product_brand, date,version, atta_id]
     * @params   $priceRows
     */

    private function BatchUpdate( Array $version,Array $rows):array
    {
        try {
            $this->db->beginTransaction();
            $model = $this->priceVersion->create($version);

            if ($model) {
                $manager = $this->manager->find($model->product_brand);

                foreach ($rows as &$row) {
                    $row["version"] = $model->id;
                    $row["version_l"] = $model->version;
                    $row["created_at"] = time();
                }

                $result = $this->db->table($manager->table)->insert($rows);

                if ($result) {
                    $this->db->commit();
                    return ["status" => "success","errmsg" => ""];
                }
            }
        }
        catch (QueryException $e)
        {
            $this->db->rollBack();
            return ["status" => "error", "errmsg" => $e->getMessage()];
        }
    }


}
