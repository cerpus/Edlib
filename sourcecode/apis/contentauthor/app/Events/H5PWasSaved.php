<?php

namespace App\Events;

use App\H5PContent;
use Illuminate\Http\Request;

class H5PWasSaved extends Event
{
    public $h5p;
    public $request;
    public $versionPurpose;
    public $oldH5pContent;

    public function __construct(H5PContent $h5p, Request $request, $versionPurpose, $oldH5pContent = null)
    {
        $this->h5p = $h5p;
        $this->request = $request;
        $this->versionPurpose = $versionPurpose;
        $this->oldH5pContent = $oldH5pContent;
    }
}
