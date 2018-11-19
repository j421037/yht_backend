<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        'App\Events\ArticleComment' => [
            'App\Listeners\SendCommentNotify'
        ],
        'App\Events\ArticleAgreeEvent' => [
            'App\Listeners\SendAgreeNotify'
        ],
        'App\Events\ArticleAnswerEvent' => [
            'App\Listeners\SendAnswerNotify'
        ],
        'App\Events\ARLogEvent' => [
            'App\Listeners\AReceLog'
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
