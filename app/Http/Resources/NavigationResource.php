<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NavigationResource extends JsonResource
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
            'name'      => $this->name,
            'classname' => $this->classname,
            'front_path'=> $this->front_path,    
            'children'  => NavigationChildResource::collection($this->children)
        ];
    }
}
