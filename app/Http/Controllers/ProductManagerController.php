<?php
/**
 * @author  wangxin
 * @date 2019.4.9
 * 价格表管理
 */

namespace App\Http\Controllers;

use App\Brand;
use App\FieldTypeItem;
use App\Http\Requests\AllocationFieldRequest;
use App\Http\Requests\UpdateFieldIndexRequest;
use App\PriceVersion;
use App\Exceptions\ManualException;
use App\Http\Requests\UpdateSortFieldRequest;
use App\ProductsManager;
use App\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductManagerStoreRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\ProductManagerListResource;
use App\Http\Requests\PriceTableAppendFieldRequest;
use Illuminate\Database\DatabaseManager;

class ProductManagerController extends Controller
{
    private $model;
    private $brand;
    private $category;
    private $db;
    private $version;
    private $fieldItem;

    public function __construct(ProductsManager $model, ProductCategory $category, Brand $brand, DatabaseManager $db, PriceVersion $version, FieldTypeItem $typeItem)
    {
        $this->model = $model;
        $this->brand = $brand;
        $this->category = $category;
        $this->db = $db;
        $this->version = $version;
        $this->fieldItem = $typeItem;
    }

    /**
     * store
     * @param request
     * @return json
     */
    public function store(ProductManagerStoreRequest $request)
    {
        try {
            $category = $this->category->find($request->category);
            $brand = $this->brand->find($request->brand);

            if (!$category)
                throw new \Exception("分类不存在");
            if (!$brand)
                throw new \Exception("品牌不存在");

            $table = strtolower("P_".$category->abbr."_".$request->table)."_prices";

            if (Schema::hasTable($table))
                throw new \Exception("该价格表已经存在");

            $sql = "CREATE TABLE IF NOT EXISTS `{$table}` ".
                    "( `id` int auto_increment primary key ,`price` decimal(20,2) not null, ".
                    "`version` tinyint not null  comment '版本号(id)',".
                    "`version_l` varchar(191) not null comment '版本号' ,".
                    "`created_at` int not null comment '创建时间戳'  ";

//            foreach ($request->attribute as $v)
//            {
//                $sql .= " , `".strtolower($v['field'])."` VARCHAR(191) not null comment '".$v['description']."' ";
//            }

            $sql .= " ) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            DB::statement($sql);

            $this->model->create([
                "category_id" => $request->category,
                "brand_id" => $request->brand,
                "brand_name"  => $brand->name,
                "table" =>$table,
                "method" => $request->post("method"),
                "columns" => json_encode([["description" => "价格","field" => "price","type" => "numeric","default" => true,"index" => 0]])
            ]);


            return response(["status" => "success"], 201);
        }
        catch (QueryException $exception)
        {
            //唯一约束错误码 1062
            if ($exception->errorInfo[1] == 1062)
                $errmsg = "价格表已经存在";
            else
                $errmsg = $exception->getMessage();
            return response(["status" => "error","errmsg" => $errmsg,], 200);
        }
        catch (\Exception $exception)
        {
            return response(["status" => "error", "errmsg" => $exception->getMessage()]);
        }
    }

    /**
     * 每个产品分类下的价格表信息
     */
    public function PriceTableList(Request $request)
    {
        if (!$request->category)
            return response(["status" => "error","errmsg" => "参数不正确"], 200);
        $data = $this->model->where(["category_id" => $request->category])->get();

        return response(["status" => "success", "data"=> ProductManagerListResource::collection($data)], 200);
    }

    /**
     *
     * drop price table
     *
     * @param $id table ids
     */
    public function PriceTableDelete(Request $request)
    {

        $tables = $this->model->whereIn("id",$request->id)->get();
        $versions = $this->version->whereIn("product_brand", $request->id)->get();
        $fieldItems = $this->fieldItem->whereIn("table_id", $request->id)->get();
        try {

            foreach ($tables as $v) {
                if ($v->delete())
                    $this->db->statement("DROP TABLE {$v->table}");
            }

            foreach ($fieldItems as $item) {
                $item->delete();
            }

            foreach ($versions as $version)
                $version->delete();

            return response(["status" => "success"], 200);
        }
        catch (QueryException $e)
        {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
    }

    public function SinglePriceTable($id)
    {
        $table = $this->model->where(["id" => $id])->get();

        return response(["status" => "success", "data"=> ProductManagerListResource::collection($table)], 200);
    }

    public function AppendField(PriceTableAppendFieldRequest $request)
    {
        $row = $this->model->find($request->id);

        if (!$row)
            return response(["status" => "error", "errmsg" => "资源不存在"], 200);

        try {

            $fields = json_decode($row->columns,true);

            $update = false;

            foreach ($fields as $k => $value) {
                if ($request->oldField == $value["field"] || $request->field == $value["field"]) {
                    unset($fields[$k]);

                    if ($request->oldField)
                        $update = true;
                    break;
                }

            }

            $field = ["description" => $request->description, "field" => $request->field,"type" => $request->type ,"key" => uniqid(), "default" => false, "index" => 0];
            array_push($fields, $field);
            $row->columns = json_encode($fields);

            if ($request->oldField == $request->field) {
                if ($row->save())
                    return response(["status" => "success"], 201);
            }

            $this->db->beginTransaction();

            if (!$update)
                $status = $this->AppendPriceTableColumn($row->table, $request->field); //添加字段
            else
                $status = $this->UpdatePriceTableColumn($row->table,$request->field,$request->oldField);

            if ($row->save() && $status) {
                $this->db->commit();
                return response(["status" => "success"], 201);
            }

        }
        catch ( QueryException $e) {
            $this->db->rollBack();
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
        catch (ManualException $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
    }

    public function DeleteField($id, $field)
    {
        $row = $this->model->find($id);

        if (!$row)
            return response(["status" => "error", "errmsg" => "资源不存在"], 200);

        $fields = json_decode($row->columns, true);

        foreach ($fields as $key => $value) {
            if ($value["field"] == $field) {
                unset($fields[$key]);
                break;
            }
        }

        $row->columns = json_encode($fields);

        try {
            $this->db->beginTransaction();

            if ($row->save())
            {
                $items = $this->fieldItem->where(["table_id" => $row->id,"field" => $field])->get();
                $flag = true;

                foreach ($items as $item) {
                    if (!$item->delete()) {
                        $flag = false;
                        break;
                    }
                }

                if ($flag && $this->DropPriceTableColumn($row->table, $field)) {
                    $this->db->commit();
                    return response(["status" => "success"], 200);
                }

                $this->db->rollBack();
                return response(["status" => "error" ,"errmsg" => "操作失败"], 200);
            }
        }
        catch (QueryException $e) {
            $this->db->rollBack();
            return response(["status" => "error", "errmsg" => "操作失败"]);
        }
    }

    protected function AppendPriceTableColumn(string $table, string $field)
    {
        //查询字段是否存在
        $sql = "Describe {$table} {$field}";

        if ($this->db->selectOne($sql))
            return false;

        $sql = "ALTER TABLE {$table} ADD {$field} VARCHAR(191) NOT NULL ";

        return $this->db->statement($sql);
    }

    protected function DropPriceTableColumn(string $table, string $field)
    {
        $sql = "ALTER TABLE {$table} DROP COLUMN  {$field}";
        return $this->db->statement($sql);
    }

    /**
     * @params string $table
     * @params string $newField
     * @params string $oldField
     * @return bool
     */
    protected function UpdatePriceTableColumn(string $table, string $newField, string $oldField)
    {
        $sql = "Describe {$table} {$newField}";

        if ($this->db->selectOne($sql))
            return false;
        $sql = "ALTER TABLE {$table} CHANGE {$oldField} {$newField} VARCHAR(191) NOT NULL";

        return $this->db->statement($sql);
    }

    public function UpdateSortField(UpdateSortFieldRequest $request)
    {
        $table = $this->model->find($request->table_id);

        if (!$table)
            return response(["status" => "error", "errmsg" => "目标不存在"], 200);

        $table->orderby = $request->field;
        $table->sort = $request->sort;

        try {
            if ($table->save())
                return response(["status" => "success"], 201);
        }
        catch (QueryException $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
    }

    public function UpdateFieldIndex(UpdateFieldIndexRequest $request)
    {
        $table = $this->model->find($request->tableId);
        $columns = json_decode($table->columns);

        foreach ($columns as &$column) {
            if ($column->field == $request->field) {

                if (property_exists($column, "index") ) {

                    if ($request->direction == "up")
                        --$column->index;
                    if ($request->direction == "down")
                        ++$column->index ;
                }
                else {
                    $column->index = 0;
                }

            }
        }

        $table->columns = json_encode($columns);
        $table->save();

        return response(["status" => "success"]);
    }

    /**
     * 分配字段
     */
    public function AllocationField(AllocationFieldRequest $request)
    {
        $table = $this->model->find($request->sourceTableId);
        $targetTable = $this->model->find($request->targetTableId);
        $targetFields = json_decode($targetTable->columns, true);
        $sourceFields = json_decode($table->columns, true);
        $field = [];

        try {

            foreach ($targetFields as $targetField) {
                if (isset($targetField["field"]) && $targetField["field"] == $request->field)
                    throw new ManualException("目标品牌已存在该字段");
            }


            foreach ($sourceFields as $sourceField) {
                if ($sourceField["field"] == $request->sourceField) {
                    $field = $sourceField;
                    break;
                }
            }

            $this->db->beginTransaction();
            array_push($targetFields, $field);

            $targetTable->columns = json_encode($targetFields);

            if ($targetTable->save()) {
                $this->AppendPriceTableColumn($targetTable->table, $field["field"]);
                //如果是列表
                if ($field["type"] == "select") {
                    $params = [];
                    $items = $this->fieldItem->where(["field" => $field["field"], "table_id" => $table->id])->get();

                    foreach ($items as $item) {
                        $timestamp = date("Y-m-d H:i:s", time());
                        array_push($params, [
                            "table_id"  => $targetTable->id,
                            "field"     => $field["field"],
                            "key"       => $item->key,
                            "value"     => $item->value,
                            "user_id"   => $this->getUserId(),
                            "created_at"=> $timestamp,
                            "updated_at"=> $timestamp
                        ]);
                    }

                    $this->fieldItem->insert($params);
                }

                $this->db->commit();
                return response(["status" => "success"], 201);
            }


        }
        catch (ManualException $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
        catch (QueryException $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
    }
}
