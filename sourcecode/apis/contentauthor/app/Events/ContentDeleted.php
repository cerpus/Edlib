<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentDeleted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $contentId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($contentId)
    {
        $this->contentId = $contentId;
    }
}
