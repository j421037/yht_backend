<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return  [
            'id'        => $this->id,
            'name'      => $this->name,
            'index'     => $this->index,
            'attr'      => $this->attr == 'protected' ? '部门' : '公共',
            'created'   => $this->created_at->format('Y-m-d')
        ];
    }
}
