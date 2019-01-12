<?php

namespace App\Http\Resources;
use App\ArticleCategory;
use App\Http\Resources\ArticleCategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalModuleAndCategoryResource extends JsonResource
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
            'attr'      => $this->attr,
            'category'  => $this->_category($this->id)
        ];
    }

    private function _category($mid)
    {
        if ($mid) {
            $list = ArticleCategory::where(['module_id' => $mid])->get();

            return ArticleCategoryResource::collection($list);
        }
    }
}
