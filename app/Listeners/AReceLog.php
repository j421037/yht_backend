<?php

namespace App\Listeners;

use App\ARLog;
use App\Events\ARLogEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AReceLog
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
     * @param  ARLogEvent  $event
     * @return void
     */
    public function handle(ARLogEvent $event)
    {
        //
        $log = new ARLog;

        $log->user_id = $event->userid;
        $log->fid = $event->fid;
        $log->type = $event->type;
        $log->model = $event->modelName;
        $log->old_value = $event->oldValue;
        $log->new_value = $event->newValue;

        $log->save();
    }
}
