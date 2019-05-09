<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerTrackResource extends JsonResource
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
            'id'                => $this->id,
            'cust_id'         	=> $this->cust_id,
			'user_id'         	=> $this->user_id,
            'content'  			=> $this->content,
            'addtime'           => $this->addtime
        ];
    }
}
