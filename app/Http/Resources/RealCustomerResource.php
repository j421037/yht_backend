<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\EnumberateItem;
use App\CustomerTrack;
use App\Project;
use App\Attachment;
use App\User;

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
            'id'                		=> $this->id,
            'name'         				=> $this->name,
			'type'						=> $this->_GetItem($this->type),
			'project'					=> $this->_GetProjects($this->id),
			'work_scope'				=> $this->_GetItem($this->work_scope),
			'project_type'				=> $this->_GetItem($this->project_type),
			'attached'					=> $this->_GetItem($this->attached),
			'coop'						=> $this->_GetItem($this->coop),
			'level'						=> $this->_GetItem($this->level),
			'pid'                		=> $this->pid,
            'user_id'         			=> $this->_GetUser($this->user_id),
//			'contract'         			=> $this->_GetPath($this->contract),.
            'contract'         			=> $this->contract,
			'tax'         				=> $this->tax,
			'account_period'         	=> $this->account_period,
            "phone"                     => $this->phone,
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
	
	//获取文件地址
	protected function _GetPath($id) {
		if ($id) {
			if ($path = Attachment::find($id)) {
				return $path->path;
			}
			return null;
		}
	}
	
	//获取业务员名字
	protected function _GetUser($id) {
		if ($id) {
			if ($user = User::find($id)) {
				return $user->name;
			}
			return null;
		}
	}
}
