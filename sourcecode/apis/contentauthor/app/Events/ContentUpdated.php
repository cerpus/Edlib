<?php

namespace App\Events;

use App\Content;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $content;
    public $oldContent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Content $content, ?Content $oldContent = null)
    {
        $this->content = $content;
        $this->oldContent = $oldContent;
    }
}
