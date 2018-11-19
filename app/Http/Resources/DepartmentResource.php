<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'user_id'   => $this->user_id,
            'user_name' => $this->userName($this->user_id)
        ];
    }

    protected function userName($id)
    {
        if ($id) {
            $user = User::find($id);

            return $user->name;
        }
    }
}
