<?php

namespace App\Http\Resources;

use App\Permission;
use App\Enumberate;
use Illuminate\Http\Resources\Json\JsonResource;

class BindAttrResource extends JsonResource
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
            'name'      => $this->name,
            'key'       => $this->key,
            'module_name'    => Permission::find($this->pid)->name,
            'module_id'    => Permission::find($this->pid)->id,
            'enumberate_name'=> Enumberate::find($this->eid)->name,
            'enumberate_id'=> Enumberate::find($this->eid)->id,
        ];
    }
}
