<?php

namespace App\Events;

use App\Content;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class ContentUpdating
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Content $content;
    public Request $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Content $content, Request $request)
    {
        $this->content = $content;
        $this->request = $request;
    }
}
