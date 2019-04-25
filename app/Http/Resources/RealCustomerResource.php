<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\EnumberateItem;
use App\CustomerTrack;
use App\Project;


class RealCustomerResource extends JsonResource
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
            'name'         		=> $this->name,
			'type'				=> $this->_GetItem($this->type),
			'project'			=> $this->_GetProjects($this->id)
        ];
    }
	
	
	/**获取枚举类型的值**/
    protected function _GetItem($id = null)
    {
        if ($id) {
            return EnumberateItem::find($id)->name;
        }
    }
	
	//获取项目信息
	protected function _GetProjects($cust_id)
    {
        if ($cust_id) {
            $project = Project::where(['cust_id' => $this->id])->get();

            if ($project) {
                return $project;
            }

        }
    }
}
