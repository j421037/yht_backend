<?php

namespace App\Http\Resources;

use App\User;
use App\Article;
use App\Department;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleAgreeResource extends JsonResource
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
            'id'    => $this->id,
            'name'  => User::find($this->agree_user_id)->name,
            'title' => Article::find($this->article_id)->title,
            'date'  => $this->created_at->format('Y-m-d H:i:s'),
            'article_id' => $this->article_id,
            'type'  => $this->_checkUser($this->agree_user_id)
        ];
    }

    protected function _checkUser($id)
    {
        //老板的赞
        // $role = User::find($id)->role()->pluck('name')->contains('Boss');
        
        // if ($role) {
        //     return 'boss';
        // }

        
        if ($department = Department::where(['user_id' => $id])->first()) {

            //老板的赞
            if ($department->name == '总经办') {
                return 'boss';
            }
            //经理的赞
            return 'manager';
        }

        return 'general';
    }
}
