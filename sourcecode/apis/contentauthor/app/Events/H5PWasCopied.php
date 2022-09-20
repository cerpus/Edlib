<?php

namespace App\Events;

use App\H5PContent;
use Illuminate\Queue\SerializesModels;

class H5PWasCopied extends Event
{
    use SerializesModels;

    public $originalH5P;
    public $newH5P;
    public $reason;

    public function __construct(H5PContent $originalH5P, H5PContent $newH5P, $reason)
    {
        $this->originalH5P = $originalH5P;
        $this->newH5P = $newH5P;
        $this->reason = $reason;
    }
}
