<?php
/**
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-29
 * Time: 16:55
 */
namespace App\Observers;
use App\AReceivebill;
use App\RealCustomer;
use App\IndexStatistics;
use App\ArrearsData;
use App\Observers\UpdateIndex;

class AReceivebillObserver {
    use UpdateIndex;
    protected $arbill;
    protected $customer;
    protected $arrears;
    protected $indexStatistics;

    public function __construct(AReceivebill $arbill, RealCustomer $customer, IndexStatistics $indexStatistics,ArrearsData $arrearsData)
    {
        $this->arbill = $arbill;
        $this->customer = $customer;
        $this->arrears = $arrearsData;
        $this->indexStatistics = $indexStatistics;
    }

    public function created(AReceivebill $receivebill)
    {
        $this->calls($receivebill);
    }
    public function saved(AReceivebill $receivebill)
    {
        $this->calls($receivebill);
    }
    public function updated(AReceivebill $receivebill)
    {
        $this->calls($receivebill);
    }
    public function deleted(AReceivebill $receivebill)
    {
        $this->calls($receivebill);
    }
    public function calls(AReceivebill $receivebill)
    {
        $row = $this->arrears->find($receivebill->rid);
        $ids = $this->arrears->where(["user_id" => $row->user_id])->get()->pluck("id");

        $data = ["completed" => 0];
        $rows = $this->arbill->whereIn("rid", $ids)->get();

        $rows->map(function($item) use (&$data) {
            $data["completed"] += $item->amountfor;
        });

        $this->rewrite($data, $row->user_id);
    }
}