<?php
/**
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-29
 * Time: 16:21
 */
namespace App\Observers;
use App\CustomerTag;
use App\Observers\UpdateIndex;
class CustomerTagObserver {
    use UpdateIndex;

    protected $tag;
    public function __construct(CustomerTag $tag)
    {
        $this->tag = $tag;
    }

    public function created(CustomerTag $tag)
    {
        $this->calls($tag);
    }
    public function updated(CustomerTag $tag)
    {
        $this->calls($tag);
    }

    public function saved(CustomerTag $tag)
    {
        $this->calls($tag);
    }
    public function deleted(CustomerTag $tag)
    {
        $this->calls($tag);
    }

    private function calls(CustomerTag $tag)
    {
        $rows = $this->tag->where(["user_id" => $tag->user_id])->get();
        $data = ["machine" => 0];
        $rows->map(function($item) use (&$data) {
            $data["machine"] += $item->num;
        });

        $this->rewrite($data, $tag->user_id);
    }
}