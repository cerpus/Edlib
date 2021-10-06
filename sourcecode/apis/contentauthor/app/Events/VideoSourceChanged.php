<?php

namespace App\Events;

use App\H5PContent;
use Illuminate\Queue\SerializesModels;

class VideoSourceChanged extends Event
{
    use SerializesModels;

    public $content, $file;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(H5PContent $content, $file)
    {
        $this->content = $content;
        $this->file = $file;
    }
}
