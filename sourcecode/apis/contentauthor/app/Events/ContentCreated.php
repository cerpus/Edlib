<?php

namespace App\Events;

use App\Content;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $content;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Content $content)
    {
        $this->content = $content;
    }
}
