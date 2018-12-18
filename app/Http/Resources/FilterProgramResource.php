<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FilterProgramResource extends JsonResource
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
            'conf'      => $this->conf,
            'default'   => $this->default,
            'fontsize'  => $this->fontsize,
            'colvisible'=> json_decode($this->col_visible, true)
        ];
    }
}
