<?php

namespace App\Listeners\H5P\Copy;

use App\Libraries\Versioning\VersionableObject;
use App\Listeners\AbstractHandleVersioning;
use App\Events\H5PWasCopied;
use Cerpus\VersionClient\VersionClient;

class HandleVersioning extends AbstractHandleVersioning
{
    protected $versionClient;

    protected $originalH5P;
    protected $newH5P;

    public function __construct(VersionClient $versionClient)
    {
        $this->versionClient = $versionClient;
    }

    public function handle(H5PWasCopied $event)
    {
        $this->originalH5P = $event->originalH5P->fresh();
        $this->newH5P = $event->newH5P->fresh();
        $reason = $event->reason;

        $this->handleSave($this->newH5P, $reason);
    }

    public function getParentVersionId()
    {
        return $this->originalH5P->version_id;
    }

    protected function getExternalUrl(VersionableObject $object)
    {
        return route('h5p.show', $object->id);
    }
}
