<?php

namespace App\Http\Resources;

use App\User;
use App\Region;
use App\Brand;
use Carbon\Carbon;
use App\CustomerNote;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerPubResource extends JsonResource
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
            // 'phone'     => $this->phone,
            'demand'    => $this->demand,
            'province'  => $this->_region($this->province),
            'city'      => $this->_region($this->city),
            'area'      => $this->_region($this->area),
            'brand'     => $this->_brand($this->brand_id),
            'time'      => $this->_time($this->id), 
            'creator'   => $this->_creator($this->create_user_id),
            // 'sort'      => $this->_sort($this->id)    
            'sort'      => $this->sort,
            'recount'   => $this->_receviceCount($this->id)  
        ];
    }

    /**
    * 返回省市级名称
    */
    protected function _region($id)
    {
        if (!Empty($id)) {

            $province = Region::select('region_name')->where(['id' => $id])->first();

            return $province->region_name;
        }
    }

    /**
    * 项目名称
    */
    protected function _brand($id)
    {
        if (!Empty($id)) {
            
            $brand = Brand::select('name')->where(['id' => $id])->first();

            return $brand->name;
        }
    }

    //
    protected function _time($id)
    {
        $note = CustomerNote::where(['customer_id' => $id, 'action' => 3])->orderBy('id', 'desc')->first();

        return Carbon::createFromTimestamp($note->created_at->timestamp)->diffForHumans();

    }

    protected function _creator($id) 
    {
        if ($id) {

            $user = User::find($id);

            return $user->name;
        }
    }
    protected function _sort($id) 
    {
        $note = CustomerNote::where(['customer_id' => $id, 'action' => 3])->orderBy('id', 'desc')->first();

        return $note->id;
    }
    //计算领取次数
    protected function _receviceCount($id)
    {
        return CustomerNote::where(['customer_id' => $id, 'action' => 0])->count();
    }
} 
