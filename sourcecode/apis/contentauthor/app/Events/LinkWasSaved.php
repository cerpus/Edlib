<?php

namespace App\Events;

use App\Link;
use Illuminate\Queue\SerializesModels;

class LinkWasSaved extends Event
{
    use SerializesModels;

    public $link;
    public $reason;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Link $link, $reason)
    {
        $this->link = $link;
        $this->reason = $reason;
    }
}
