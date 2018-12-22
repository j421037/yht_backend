<?php

namespace App\Http\Resources;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticlePortalResource extends JsonResource
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
            'id'        => $this->id,
            'title'     => $this->title,
            'titlepic'  => $this->titlepic,
            'abstract'  => $this->abstract,
            'user'      => User::find($this->user_id)->name,
            'created'   => Carbon::createFromTimestamp(strtotime($this->updated_at))->diffForHumans(),
            'agree'     => $this->ArticleData->agrees,
            'isfine'    => $this->ArticleData->isFine
        ];
    }
}
