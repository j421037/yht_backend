<?php

namespace App\Http\Resources;

use App\User;
use App\EnumberateItem;
use Illuminate\Http\Resources\Json\JsonResource;

class PotentialListResource extends JsonResource
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
            'name'      => $this->name,
            'project'   => $this->project,
            'user_name' => $this->GetUser($this->user_id),
            'tag'       => $this->GetItem($this->tag),
            'estimate'  => $this->estimate > 0 ? number_format($this->estimate) : $this->estimate,
            'index'     => $this->index,
            'protype'   => $this->GetItem($this->tid), //施工范围 = 项目属性
            'nameshow'  => $this->nameshow
        ];
    }

    protected function GetUser($userid)
    {
        if ($userid) {
            return User::find($userid)->name;
        }
    }

    public function GetItem($id)
    {
        if ($id) {
            return EnumberateItem::find($id)->name;
        }
    }
}
