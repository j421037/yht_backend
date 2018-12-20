<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumModuleAllResource extends JsonResource
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
            'id'   => $this->id,
            'name'  => $this->name,
            'attr'  => $this->attr
        ];
    }
}
