<?php

namespace App\Http\Resources;

use App\Department;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id'            => $this->id,
            'name'          => $this->name,
            'headimg'       => $this->headimg,
            'department'    => $this->department($this->department_id)
        ];
    }

    protected function department($id)
    {
        if (!Empty($id)) {

            $list = Department::find($id);

            return $list->name;
        }
    }
}
