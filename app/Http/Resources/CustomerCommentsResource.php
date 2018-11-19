<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerCommentsResource extends JsonResource
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
            'content'   => $this->content,
            'user_name' => $this->_user($this->user_id),
            'headimg'   => $this->_headImg($this->user_id),
            'updated'   => $this->updated_at->format('Y-m-d H:i:s')
        ];

    }

    protected function _user($id)
        {
            if ($id) {
                return User::find($id)->name;
            }
        }

    protected function _headImg($id)
    {
        if ($id) {
            return User::find($id)->headimg;
        }
    }
}
