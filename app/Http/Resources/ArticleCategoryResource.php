<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCategoryResource extends JsonResource
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
            'created'   => $this->created_at->format('Y-m-d H:i:s'),
            'updated'   => $this->updated_at->format('Y-m-d H:i:s'),
            'user'      => $this->_user($this->user_id),
            'department'=> $this->department->name
        ];
    }

    protected function _user($id) 
    {
        return User::find($id)->name;
    }
}
