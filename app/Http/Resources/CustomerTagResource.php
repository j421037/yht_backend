<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\EnumberateItem;

class CustomerTagResource extends JsonResource
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
            'id'                => $this->id,
            'cust_id'         	=> $this->cust_id,
			'user_id'         	=> $this->user_id,
            'machine'  			=> $this->_GetItem($this->machine),
			'num'  				=> $this->num,
			'remark'  			=> $this->remark,
            'addtime'           => $this->addtime
        ];
    }
	
	/**获取枚举类型的值**/
    protected function _GetItem($id = null)
    {
        if ($id) {
            return EnumberateItem::find($id)->name;
        }
    }
}
