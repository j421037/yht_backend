<?php

namespace App\Listeners;

use App\ArticleNotify;
use App\Events\ArticleAgreeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAgreeNotify
{
    
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
       
    }

    /**
     * Handle the event.
     *
     * @param  ArticleAgree  $event
     * @return void
     */
    public function handle(ArticleAgreeEvent $event)
    {
        //
        $notify = new ArticleNotify;
        $notify->article_id = $event->agree->article_id;
        $notify->sender = $event->agree->agree_user_id;
        $notify->receiver = $event->agree->create_user_id;
        $notify->save();
    }
}
