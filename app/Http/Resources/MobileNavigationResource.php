<?php

namespace App\Http\Resources;

use App\Http\Resources\MobileNavigationChildrenResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileNavigationResource extends JsonResource
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
            'name'          => $this->mobile_name,
            'icon'          => $this->mobile_classname,
            'mobile_path'   => $this->mobile_path,
            'show_mobile'   => $this->show_mobile,
            'children'      => MobileNavigationChildrenResource::collection($this->children)
        ];
    }
}
