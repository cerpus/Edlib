<?php

namespace App\Events;

use App\H5PContent;
use Illuminate\Http\Request;

class H5PWasSaved extends Event
{
    public $h5p;
    public $request;

    public function __construct(H5PContent $h5p, Request $request)
    {
        $this->h5p = $h5p;
        $this->request = $request;
    }
}
