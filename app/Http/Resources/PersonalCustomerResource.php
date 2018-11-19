<?php

namespace App\Http\Resources;

use Carbon;
use App\User;
use App\Region;
use App\Brand;
use App\Department;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalCustomerResource extends JsonResource
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
            'phone'         => $this->phone,
            'wechat'        => $this->wechat,
            'qq'            => $this->qq,
            'project_name'  => $this->project_name,
            'demand'        => $this->demand,
            'description'   => $this->description,
            'accept'        => $this->accept,
            // 'updated_at'    => $this->updated_at->format('Y-m-d H:i:s'),
            'province'      => $this->_province($this->province),
            'city'          => $this->_province($this->city),
            'area'          => $this->_province($this->area),
            'brand'         => $this->_brand($this->brand_id),
            'department'    => $this->department_id ?? 0,
            'creator'       => $this->_creator($this->create_user_id),
            'sort'          => $this->sort,
            'action_date'   => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }

    protected function _province($id)
    {
        if ($id) {

            return Region::find($id)->region_name;
        }
    }

    protected function _brand($id)
    {
        if ($id) {
            
            return Brand::find($id)->name;
        }
    }

    protected function _creator($id)
    {
        if ($id) {

            return User::find($id)->name;
        }
    }
   
}
