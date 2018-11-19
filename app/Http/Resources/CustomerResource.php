<?php

namespace App\Http\Resources;

use App\User;
use App\Brand;
use App\Region;
use App\CustomerNote;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $receive = CustomerNote::where(['customer_id' => $this->id, 'action' => 0])->orderBy('id', 'desc')->first();
        $accept = CustomerNote::where(['customer_id' => $this->id, 'action' => 1])->orderBy('id', 'desc')->first();

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'phone'         => $this->phone,
            'wechat'        => $this->wechat,
            'qq'            => $this->qq,
            'brand_name'    => $this->_band($this->brand_id),
            // 'province'      => $province['region_name'].' '.$city['region_name'].' '.$area['region_name'],
            'province'      => $this->_GetProvince($this->province),
            'city'          => $this->_GetProvince($this->city),
            'area'          => $this->_GetProvince($this->area),
            'project_name'  => $this->project_name,
            'description'   => $this->description,
            'publish'       => $this->publish, //发布状态
            'user'          => $this->_user($this->user_id),
            'accept'        => $this->accept,
            'created'       => $this->created_at->format('Y-m-d H:i:s'),
            'accept_date'   => Empty($accept) ? null : $accept->created_at->format('Y-m-d H:i:s'),//验收日期
            'receive_date'  => Empty($receive) ? null : $receive->created_at->format('Y-m-d H:i:s'), //领取日期
            'demand'        => $this->demand
        ];
    }

    private function _user($userid)
    {
        $user = null;

        if ($userid) {
            $user = User::find($userid)->name;
        }

        return $user;
    }

    private function _band($bid)
    {   
        $list = [];

        if ($bid) {
            $list = Brand::find($bid)->name;
        }

        return $list;
    }

    /**
    * @param $pid 省市区对应的id
    * 
    */
    private function _GetProvince($pid)
    {
        $list = [];

        if ($pid) {
            $list = Region::select('region_name', 'id')->find($pid);
        }

        return $list;
    }


}
