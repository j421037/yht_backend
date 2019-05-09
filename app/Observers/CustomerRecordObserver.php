<?php
/**
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-29
 * Time: 16:40
 */
namespace App\Observers;
use App\CustomerRecord;
use App\Observers\UpdateIndex;
class CustomerRecordObserver {
    use UpdateIndex;
    protected $record;

    public function __construct(CustomerRecord $record)
    {
        $this->record = $record;
    }

    public function created(CustomerRecord $record)
    {
        $this->calls($record);
    }

    public function saved(CustomerRecord $record)
    {
        $this->calls($record);
    }

    public function updated(CustomerRecord $record)
    {
        $this->calls($record);
    }

    public function deleted(CustomerRecord $record)
    {
        $this->calls($record);
    }

    public function calls(CustomerRecord $record)
    {
        $rows = $this->record->where(["user_id" => $record->user_id])->get();
        $data = ["censor" => 0];
        $rows->map(function($item) use (&$data) {
            $data["censor"] += $item->num;
        });

        $this->rewrite($data, $record->user_id);
    }
}