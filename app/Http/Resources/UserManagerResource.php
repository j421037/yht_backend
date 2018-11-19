<?php

namespace App\Http\Resources;

use App\Department;
use Illuminate\Http\Resources\Json\JsonResource;

class UserManagerResource extends JsonResource
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
            'username'      => $this->name,
            'date'          => $this->created_at->format('Y-m-d H:i:s'),
            'authorize'     => $this->authorize,
            'phone'         => $this->phone,
            'role'          => $this->formatRole($this->role),
            'department'    => $this->formatDepart($this->department_id),
            'workwx'        => $this->workwx,
        ];
    }



    /**
    * 格式化 权限 列表
    */
    protected function formatRole($role) 
    {
        if (!Empty($role)) {

            $i = 0;
            $arr = array();
            
            foreach ($role as $k => $v) {

                $arr[$i]['label'] = $v->name;
                ++$i;
            }

            return $arr;
        }
    }



    protected function formatDepart($id)
    {
        $list = Department::select('name')->find($this->department_id);

        if (!Empty($list)) {
            
            return $list->name;
        }
    }
}
