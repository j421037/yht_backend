<?php
/**
 * ob customer model change
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-29
 * Time: 10:17
 */
namespace App\Observers;

use App\User;
use App\Project;
use App\RealCustomer;
use App\Enumberate;
use App\ArrearsData;
use App\EnumberateItem;
use App\Observers\UpdateIndex;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;

class RealCustomerObserver {
    use UpdateIndex;

    /**
     *  models
     */
    protected $db;
    protected $user;
    protected $enum;
    protected $enumItem;
    protected $project;
    protected $arrearsData;
    protected $map = [
        "target_client" => "目标客户" ,
         "coop_client"=> "合作客户",
         "report_client"=>"报备客户",
         "lose_client"=> "流失客户"
    ];

    public function __construct(
        Enumberate $enum,
        DatabaseManager $db,
        ArrearsData $arrearsData,
        User $user,
        Project $project,
        EnumberateItem $item
    )
    {
        $this->db = $db;
        $this->enum = $enum;
        $this->user = $user;
        $this->project = $project;
        $this->enumItem = $item;
        $this->arrearsData = $arrearsData;
    }

    /**
     * create event
     */
    public function created(RealCustomer $model)
    {
        $this->calls($model);
        $this->CreateArrears($model);
    }

    public function saved(RealCustomer $model)
    {
        $this->calls($model);
        $this->CreateArrears($model);
    }

    /**
     * deleted event
     */
    public function deleted(RealCustomer $model)
    {
        $this->calls($model);
    }
    /**
     * updated event
     */
    public function updated(RealCustomer $model) {
        $this->calls($model);
        $this->CreateArrears($model);
    }

    private function calls(RealCustomer $model)
    {
        $data = [];
        $enum = $this->enum->where(["name" => "客户类型"])->with(["item"])->first();
        $result = $this->db->select("select type,count(*) as counts from real_customers where user_id = :userid group by type",["userid" => $model->user_id]);

        foreach ($enum->item as $v)
        {
            foreach ($result as $vv)
            {
                if ($vv->type == $v["id"])
                {
                    $key = array_search($v["name"], $this->map);

                    if ($key)
                    {
                        $data[$key] = $vv->counts;
                    }
                }
            }
        }

        $this->rewrite($data,$model->user_id);
    }

    /**
     *  create a arrear data
     */
    private function CreateArrears(RealCustomer $realCustomer):void
    {
        // is coop customer
        $enuitem = $this->enumItem->find($realCustomer->type);

        if (!isset($enuitem->name) && $enuitem->name != "合作客户")
            return ;

        $project = $this->project->find($realCustomer->pid);
        $row = $this->arrearsData->where(["customer_name" => $realCustomer->name,"project_name" => $project->name, "work_scope" => $realCustomer->work_scope])->first();

        if (!$row)
            $row = $this->arrearsData->newInstance();

        $user = $this->user->find($realCustomer->user_id);
        $scope = $this->enumItem->find($realCustomer->work_scope);

        $row->customer_name = $realCustomer->name;
        $row->customer_id = $realCustomer->id;
        $row->project_name = $project->name;
        $row->project_id = $project->id;
        $row->user_name = $user->name;
        $row->user_id = $user->id;
        $row->contract = $realCustomer->contract ?? 0;
        $row->attached = $realCustomer->attached ?? 0;
        $row->tax = $realCustomer->tax ?? 0;
        $row->account_period = $realCustomer->account_period ?? 0;

        if ($scope) {
            $row->work_scope = $scope->id;
            $row->work_scope_name = $scope->name;
        }

        $row->save();

    }
}