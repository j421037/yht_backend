<?php

namespace App\Listeners;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Events\ArticleComment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCommentNotify
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
     * @param  ArticleComment  $event
     * @return void
     */
    public function handle(ArticleComment $event)
    {
        $msg = '为每个事件和监听器手动创建文件是件很麻烦的事情'.time();
        $log = new Logger('EventsTest');

        $log->pushHandler(
            new StreamHandler(
                storage_path('logs/event/'.date('Y-m-d').'.log'), 
                Logger::INFO
            )
        );

        $log->addInfo($msg);
    }
}
