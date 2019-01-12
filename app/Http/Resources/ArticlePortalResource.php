<?php

namespace App\Http\Resources;

use App\User;
use Carbon\Carbon;
use App\ArticleCategory;
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
            'isfine'    => $this->ArticleData->isFine,
            'top'       => (bool) $this->top,
            'category'  => $this->category_id > 0 ? ArticleCategory::find($this->category_id)->name : '默认',
            'category_id'=> $this->category_id
        ];
    }
}
