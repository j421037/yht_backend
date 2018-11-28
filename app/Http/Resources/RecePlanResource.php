<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecePlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id"        => $this->id,
            'content'   => $this->content,
            'week'      => $this->week,
            'date'      => $this->weekToDate($this->week, $this->created_at->timestamp),

        ];
    }

    /**
    * @param $week 第几周
     *@return $date string 当前周的开始时间和结束时间
     */
    protected  function weekToDate($week, $timestamp)
    {
        if ($week < 10) {
            $week = '0'.$week;
        }

        $year = date('Y', $timestamp);

        $start = strtotime($year. 'W'. $week);
        $end = strtotime("+1 week -1 day", $start);
        return date('Y-m-d',$start).' 至 '. date('Y-m-d', $end);
    }
}
