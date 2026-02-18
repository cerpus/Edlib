<?php

namespace App\Listeners\H5P\Copy;

use App\Events\H5PWasCopied;
use App\Listeners\AbstractHandleVersioning;

class HandleVersioning extends AbstractHandleVersioning
{
    protected $originalH5P;
    protected $newH5P;

    public function handle(H5PWasCopied $event)
    {
        $this->originalH5P = $event->originalH5P->fresh();
        $this->newH5P = $event->newH5P->fresh();

        $this->handleSave($this->newH5P, $event->reason);
    }

    public function getParentVersionId()
    {
        return $this->originalH5P->version_id;
    }
}
