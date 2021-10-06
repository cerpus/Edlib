<?php

namespace App\Events;

use App\Libraries\DataObjects\EdlibResourceDataObject;
use App\Libraries\DataObjects\ResourceDataObject;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ResourceSaved extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const CREATE = "create";
    const UPDATE = "update";

    public $resourceData;
    public $edlibResourceDataObject;

    /**
     * Create a new job instance.
     *
     * @param ResourceDataObject $dataObject
     * @param EdlibResourceDataObject $edlibResourceDataObject
     */
    public function __construct(ResourceDataObject $dataObject, EdlibResourceDataObject $edlibResourceDataObject)
    {
        $this->resourceData = $dataObject;
        $this->edlibResourceDataObject = $edlibResourceDataObject;
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
