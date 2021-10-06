<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class ContentCreating
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

}
