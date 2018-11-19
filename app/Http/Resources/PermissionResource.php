<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Api返回数据预处理
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'method'        => $this->method,
            'backend_path'  => $this->backend_path,
            'front_path'    => $this->front_path,
            'description'   => $this->description,
            'node_type'     => $this->node_type,
            'pid'           => $this->pid,
            'classname'     => $this->classname,
            'parentNode'    => $this->parentNode,
            'show_pc'       => $this->show_pc,
            'show_mobile'   => $this->show_mobile,
            'mobile_name'   => $this->mobile_name,
            'mobile_classname'          => $this->mobile_classname,
            'mobile_path'               => $this->mobile_path,
            'template_pc_name'          => $this->template_pc_name,
            'template_mobile_name'      => $this->template_mobile_name,
            'show_pc'                   => $this->show_pc,
            'show_mobile'               => $this->show_mobile,
            'mobile_sort'               => $this->mobile_sort,
            'pc_sort'                   => $this->pc_sort
        ];
    }
}
