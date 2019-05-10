<?php

namespace App\Http\Controllers;

use App\ArrearsData;
use App\EnumberateItem;
use App\Http\Resources\ArrearSearchNameResource;
use Illuminate\Http\Request;

class ArrearsController extends Controller
{
    private $model;
    private $enum;

    public function __construct(ArrearsData $arrearsData, EnumberateItem $enum)
    {
        $this->model = $arrearsData;
        $this->enum = $enum;
    }

    /**
     * update row status
     */
    public function updateStatus(Request $request)
    {
        $ids = $this->UserAuthorizeIds();
        $row = $this->model->find($request->id);
        $item = $this->enum->find($request->status);

        try {
            if (!$item)
                throw new \Exception("参数不完整");

            if (!$row)
                throw new \Exception("目标不存在");

            if (!in_array($row->user_id,$ids))
                throw new \Exception("没有权限访问该资源");

            $row->status = $item->id;
            $row->status_name = $item->name;

            if ($row->save())
                return response(["status" => "success"], 200);
        }
        catch (\Exception $e) {
            return response(["status" => "err","errmsg" => $e->getMessage()],200);
        }
    }

    public function searchName(Request $request)
    {
        if (empty($request->word))
            return;
        switch ($request->key) {
            case "customer_name" :
                $field = "customer_name";
                break;
            case "project_name" :
                $field = "project_name";
                break;
            default :
                $field = "customer_name";
                break;
        }

        $keys = [];
        $result = [];
        $rows = $this->model->where($field, "like", "%".$request->word."%")->get();

        if ($rows) {
            $keys = $rows->groupBy($field)->keys()->toArray();
        }

        foreach ($keys as $v) {
            array_push($result,["value" => $v]);
        }

        return response(["data" => [$request->key => $result], "status" => "success"], 200);
    }
}
