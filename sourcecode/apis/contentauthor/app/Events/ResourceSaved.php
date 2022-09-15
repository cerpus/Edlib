<?php

namespace App\Events;

use Cerpus\EdlibResourceKit\Contract\DraftAwareResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceSaved extends Event
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(private DraftAwareResource $resource)
    {
    }

    public function getResource(): DraftAwareResource
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
