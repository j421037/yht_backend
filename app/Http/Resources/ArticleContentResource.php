<?php

namespace App\Http\Resources;

use Auth;
use App\User;
use Carbon\Carbon;
use App\ArticleAgree;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleContentResource extends JsonResource
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
            'title'     => $this->title,
            'body'      => $this->body,
            'created'   => Carbon::createFromTimestamp($this->created_at->timestamp)->diffForHumans(),
            'name'      => User::find($this->user_id)->name
            // 'alreadyAgree' => $this->_checkAgree($this->id, Auth::user()->id),
        ];
    }

}
