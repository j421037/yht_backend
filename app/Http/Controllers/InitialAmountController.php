<?php

namespace App\Http\Controllers;

use App\InitialAmount;
use App\AReceivable;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests\InitialAmountStoreRequest;
use App\Http\Resources\InitialAmountAllResource;

class InitialAmountController extends Controller
{
    protected $model;
    protected $arble;

    public function __construct(InitialAmount $initialAmount, AReceivable $receivable)
    {
        $this->arble = $receivable;
        $this->model = $initialAmount;
    }

    /**
     * @return Collection  all initial data from customer
     */
    public function all(Request $request)
    {
        if (!$request->rid && !is_numeric($request->rid))
            return response(["status" => "error","errmsg" => "参数不正确"], 200);

        $offset = empty($request->limit) ? 0 : $request->limit;
        $limit = empty($request->offset) ? 5 : $request->offset;
        $model = $this->model->where(["rid" => $request->rid]);
        $rows = $model->limit($limit)->offset($offset)->orderBy("id","desc")->get();
        $total = $model->count();

        return response(["data" => InitialAmountAllResource::collection($rows),"total" => $total], 200);
    }

    /**
     * create
     */
    public function store(InitialAmountStoreRequest $request)
    {
        try {
            $data = $request->all();
            $data["date"] = strtotime($data["date"]);
            $rows = $this->arble->where(["rid" => $request->rid])->orderBy("date")->first();

            if ($rows && $rows->date <= $data["date"])
                throw new \Exception("期初日期必须在 ".date("Y-m-d",$rows->date)." 之前");

            $this->model->create($data);

            return response(["status" => "success"], 201);
        }
        catch (QueryException $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 201);
        }
        catch (\Exception $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 201);
        }
    }

    /**
     * update
     */
    public function update(InitialAmountStoreRequest $request)
    {
        try {
            $date = strtotime($request->date);
            $row = $this->arble->where(["rid" => $request->rid])->orderBy("date")->first();
            $self = $this->model->find($request->id);

            if (!$self)
                throw new \Exception("目标不存在");

            if ($row && $row->date <= $date)
                throw new \Exception("期初日期必须在 ".date("Y-m-d", $row->date)." 之前");

            $self->amountfor = $request->amountfor;
            $self->date = $date;
            $self->remark = $request->remark;
            $self->type = $request->type;
            $self->save();

            return response(["status" => "success"], 200);
        }
        catch (QueryException $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 201);
        }
        catch (\Exception $e) {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 201);
        }
    }
}
