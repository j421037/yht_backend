<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ARLogEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userid;
    public $fid;
    public $type;
    public $modelName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $userid, string $fid, string $type, string $modelName, string $oldValue, string $newValue)
    {
        //
        $this->userid = $userid;
        $this->fid = $fid;
        $this->type = $type;
        $this->modelName = $modelName;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
