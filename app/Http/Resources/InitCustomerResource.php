<?php

namespace App\Http\Resources;

use App\User;
use App\Customer;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class InitCustomerResource extends JsonResource
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
        $user = $this->_user($this->user_id);
        $customer = $this->_customer($this->customer_id);

        return [
            'headimg'   => $user->headimg ?? 'http://e.yhtjc.com/v2/public/img/default.png',
            'name'      => $user->name,
            'demand'    => $customer->demand,
            'action'    => $this->_action($this->action),
            'date'      => Carbon::createFromTimestamp($this->updated_at->timestamp)->diffForHumans()
        ];  
    }

    protected function _user($id) 
    {
        return User::find($id);
    }

    protected function _customer($id)
    {
        return Customer::find($id);
    }
    protected function _action($action)
    {
        switch($action) {
            case 0:
                # code...
                return '领取';
                break;
            case 1:
                # code...
                return '验收';
                break;
            case 2:
                return '释放';
                break;
            default :
                return '发布';
                break;
        }
    }
}
