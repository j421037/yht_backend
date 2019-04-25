<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\EnumberateItem;
use App\Brand;
use App\User;
use App\CustomerTrack;

class ProjectResource extends JsonResource
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
            'id'                	=> $this->id,
            'name'         			=> $this->name,
            'cust_id'        		=> $this->cust_id,
            'user_id'           	=> $this->user_id,
			'user_name'           	=> $this->_user($this->user_id),
			'phone_num'				=> $this->phone_num,
			'tag'					=> $this->tag,
			'attachment_id'			=> $this->attachment_id,
			'tax'               	=> $this->tax,
            'tid'         			=> $this->tid,
            'affiliate'        		=> $this->affiliate,
            'agreement'          	=> $this->agreement,
			'estimate'				=> $this->estimate,
			'payment_days'			=> $this->payment_days,
			'payment_start_date'	=> $this->payment_start_date,
			'last_payment_date'     => $this->last_payment_date,
            'isclose'        		=> $this->isclose,
			'brand'        			=> $this->brand,
            'type'          		=> $this->_GetItem($this->type),
			'addr'        			=> $this->addr,
            'start_at'  	 		=> $this->start_at,
            'finish_at'         	=> $this->finish_at,
			'work_scope'			=> $this->_GetItem($this->work_scope),
			'project_type'			=> $this->_GetItem($this->project_type),
			'attached'				=> $this->_GetItem($this->attached),
			'tags'              	=> $this->_GetItem($this->tags),
            'contract'         		=> $this->contract,
            'account_period'    	=> $this->account_period,
            'tax'           		=> $this->tax,
			'coop'					=> $this->_GetItem($this->coop),
			'track'					=> $this->_track($this->id),
            'status'				=> $this->status,
			'addr'					=> $this->addr,
			'addr_detail'			=> $this->addr_detail
        ];
    }
	
	
	/**获取枚举类型的值**/
    protected function _GetItem($id = null)
    {
        if ($id) {
            return EnumberateItem::find($id)->name;
        }
    }
	
	//品牌库
	/*
	protected function _brand()
    {
        $brand = Brand::whereIN('id', explode(',',$this->brand))->get()->pluck("name")->toArray();

		if ($brand) {
			return $brand;
		}
    }
	*/
	
	//动态跟踪
	protected function _track($cust_id)
    {
        if ($cust_id) {
            $track = CustomerTrack::where(['cust_id' => $cust_id])->orderBy('id', 'desc')->first();

            if ($track) {
                return $track->content;
            }

        }
    }
	
	//获取销售员名字
	protected function _user($user_id)
    {
        if ($user_id) {
            $user = User::where(['id' => $user_id])->first();

            if ($user) {
                return $user->name;
            }

        }
    }
}
