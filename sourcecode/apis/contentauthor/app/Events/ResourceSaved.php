<?php

namespace App\Events;

use Cerpus\EdlibResourceKit\Contract\EdlibResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceSaved extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(private EdlibResource $resource)
    {
    }

    public function getResource(): EdlibResource
    {
        return $this->resource;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
