<?php

namespace App\Http\Resources;

use Auth;
use App\User;
use App\Article;
use App\ArticleAnswer;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleAnswerNotifyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $article = Article::find($this->article_id);
        $answer = ArticleAnswer::find($this->answer_id);
        // return parent::toArray($request);
        return [
            'id'        => $this->id,
            // 'sender'    => $this->sender,
            'article_id'=> $this->article_id,    
            'title'     => $article->title,
            'message'   => $this->_buildMessage($this->sender, $this->receiver),
            'body'      => $answer->content,
            'created'   => Carbon::createFromTimestamp($this->created_at->timestamp)->diffForHumans()
        ];
    }

    //返回文章内容前85个字符
    protected function _buildContent($content)
    {
        $content = strip_tags($content);

        return mb_substr($content, 0 ,85).'...';
    }

    //构建提醒信息
    protected function _buildMessage($sender, $receiver)
    {
        $author = Auth::user()->id;

        //发送人等于自己 ==== 我回复别人
        if ($author == $sender) {

            //自己赞自己
            $receiverName = '我';

            if ($sender != $receiver) {
                $receiverName = User::find($receiver)->name;
            }

            return '我回复了 '. $receiverName . ' 的文章';
        } else {
            //发送人不等于自己  别人回复我
            return User::find($sender)->name . ' 回复了 我 的文章';
        }

    }
}
