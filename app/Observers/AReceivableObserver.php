<?php
/**
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-29
 * Time: 17:11
 */
namespace App\Observers;
use App\RealCustomer;
use App\AReceivable;
use App\ArrearsData;
use App\Observers\UpdateIndex;
class AReceivableObserver {
    use UpdateIndex;
    protected $customer;
    protected $arble;
    protected $arrears;

    public function __construct(RealCustomer $customer, AReceivable $arble, ArrearsData $arrearsData)
    {
        $this->customer = $customer;
        $this->arble = $arble;
        $this->arrears = $arrearsData;
    }

    public function created(AReceivable $receivable)
    {
        $this->calls($receivable);
    }
    public function saved(AReceivable $receivable)
    {
        $this->calls($receivable);
    }
    public function updated(AReceivable $receivable)
    {
        $this->calls($receivable);
    }
    public function deleted(AReceivable $receivable)
    {
        $this->calls($receivable);
    }
    public function calls(AReceivable $receivable)
    {
        $row = $this->arrears->find($receivable->rid);
        $ids = $this->arrears->where(["user_id" => $row->user_id])->get()->pluck("id");

        $data = ["completed" => 0];
        $rows = $this->arble->whereIn("rid", $ids)->get();

        $rows->map(function($item) use (&$data) {
            $data["completed"] += $item->amountfor;
        });

        $this->rewrite($data, $row->user_id);
    }
}