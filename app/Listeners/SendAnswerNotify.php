<?php

namespace App\Listeners;

use App\ArticleNotify;
use App\Events\ArticleAnswerEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAnswerNotify
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ArticleAnswerEvent  $event
     * @return void
     */
    public function handle(ArticleAnswerEvent $event)
    {
        //
        $notify = new ArticleNotify;

        $notify->article_id = $event->answer->article_id;
        $notify->sender = $event->answer->user_id;
        $notify->answer_id = $event->answer->id;
        $notify->receiver = $event->receiver;
        $notify->type = 1;
        $notify->save();
    }
}
