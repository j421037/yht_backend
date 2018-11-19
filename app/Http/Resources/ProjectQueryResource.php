<?php

namespace App\Http\Resources;

use App\AReceivable;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectQueryResource extends JsonResource
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
            'label'     => $this->name,
            'value'     => $this->id,
            'custid'    => $this->cust_id,
            'endInit'   => $this->_endInit($this->id), //是否有期初应收数据
        ];
    }

    /**查询是否有期初**/
    protected function _endInit($id) 
    {
        $list = AReceivable::where(['pid' => $id, 'is_init' => 1])->first();

        return  (bool)$list;
    }
}
