<?php

namespace App\Http\Resources;

use App\Region;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOneResource extends JsonResource
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
            'phone'     => $this->phone,
            'accept'    => $this->accept,
            'area'      => $this->area,
            'brand_id'  => $this->brand_id,
            'city'      => $this->city,
            'create_user_id'    => $this->create_user_id,
            'created_at'        => $this->created_at,
            // 'deleted_at'        => $this->deleted_at,
            'demand'            => $this->demand,
            'department_id'     => $this->department_id,
            'description'       => $this->description,
            'project_name'      => $this->project_name,
            'province'          => $this->province,
            'publish'           => $this->publish,
            'qq'                => $this->qq,
            'updated_at'        => $this->updated_at,
            'user_id'           => $this->user_id,
            'wechat'            => $this->wechat,
            'province_code'     => $this->_regionCode($this->province), 
            'city_code'         => $this->_regionCode($this->city), 
            'area_code'         => $this->_regionCode($this->area), 
            'province_name'     => $this->_regionName($this->province), 
            'city_name'         => $this->_regionName($this->city), 
            'area_name'         => $this->_regionName($this->area), 
        ];
    }

    protected function _regionCode($id) 
    {
        return Region::find($id)->region_code;
    }
    protected function _regionName($id) 
    {
        return Region::find($id)->region_name;
    }
}
