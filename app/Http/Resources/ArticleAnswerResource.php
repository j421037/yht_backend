<?php

namespace App\Http\Resources;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'        => $this->id,
            'content'   => $this->content,
            'true_time' => $this->created_at->format('Y-m-d H:i:s'),
            'name'      => User::find($this->user_id)->name,
            'created'   => Carbon::createFromTimestamp($this->created_at->timestamp)->diffForHumans()
        ];
    }
}
