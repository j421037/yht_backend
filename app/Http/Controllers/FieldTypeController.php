<?php

namespace App\Http\Controllers;

use App\Exceptions\ManualException;
use App\FieldTypeItem;
use App\Http\Requests\FieldTypeAppendItemRequest;
use App\Http\Resources\FieldTypeItemResource;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class FieldTypeController extends Controller
{
    protected $fields = [
        ["label" => "数字",   "value" => "numeric"],
        ["label" => "字符串",  "value" => "string"],
        ["label" => "下拉列表", "value" => "select"]
    ];

    protected $model;

    public function __construct(FieldTypeItem $typeItem)
    {
        $this->model = $typeItem;
    }

    public function all()
    {
        return response(["status" => "success", "data" => $this->fields], 200);
    }

    /**
     * 下拉列表增加项
     */
    public function AppendItem(FieldTypeAppendItemRequest $request)
    {
        $model = $this->model->newInstance();
        $model->field = $request->field;
        $model->value = $request->value;
        $model->key = $request->key;
        $model->user_id = $this->getUserId();
        $model->table_id = $request->tableId;

        try {
            $row = $this->model->where(["table_id" => $model->tableId,"field" => $model->field])->first();

            if ($row)
                throw new ManualException("该选项已经存在");

            if ($model->save())
                return response(["status" => "success"], 201);
        }
        catch (QueryException $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
        catch (ManualException $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }

    }

    public function Items($tableId, $field)
    {
        $rows = $this->model->where(["table_id" => $tableId, "field" => $field])->orderBy("key")->get();

        return response(["status" => "success", "data" => FieldTypeItemResource::collection($rows)],200);
    }

    public function DeleteItem($id)
    {
        $row = $this->model->find($id);
        if ($row->delete($id))
            return response(["status" => "success"], 200);
        return response(["status" => "error", "errmsg" => "操作失败", "data" => $id]);
    }
}
