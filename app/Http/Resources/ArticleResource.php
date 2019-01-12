<?php

namespace App\Http\Resources;

use Storage;
use App\ArticleCategory;
use App\User;
use App\ArticleData;
use Carbon\Carbon;
use App\ForumModuleMappingDepartment as FMapping;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'id'        => $this->id,
            'title'     => $this->title,
            'titlepic'  => $this->titlepic,
            'abstract'  => $this->abstract,
            'user'      => $this->_user($this->user_id),
            'created'   => Carbon::createFromTimestamp(strtotime($this->updated_at))->diffForHumans(),
            'agree'     => $this->_argee($this->id),
            'isfine'    => $this->isFine,
            'module'      => $this->_divide($this->module_id)
        ];
    }

    protected function _user($id) 
    {
        return User::find($id)->name;
    }

    protected function _argee($id)
    {
        $data = ArticleData::find($id);
        $agree = 0;
        if (!$data) {
            $agree = 0;
        }
        else {
            $agree = $data->agrees;
        }

        return $agree;
    }

    protected function _getUrl($url)
    {
        if ($url) {
            // return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).Storage::disk('public')->url($url);
            return 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).Storage::url($url);
        }
    }

    protected function _Category($id)
    {
        return ArticleCategory::find($id)->name;
    }

    /**
    * 摘要截取正文前100个字符 图片除外
    */
    protected function _ArticleAbstract($text, $titlepic)
    {
        $length = 100; //有图的情况下截取前100个字符

        if (!$titlepic) {
            $length = 80; //美图的情况下截取前80个字符
        }

        $text = mb_substr(strip_tags($text), 0, $length);
        $patt = "/[^\s].*/";
        preg_match_all($patt, $text, $result);

        return $result[0][0].'...';
    }
    //划分文章类别
    protected function _divide($moduleId)
    {
        if ($moduleId) {
            $module = FMapping::find($moduleId);

            if ($moduleId) {
                return ['name' => $module->name, 'attr' => $module->attr];
            }

        }
    }
}
