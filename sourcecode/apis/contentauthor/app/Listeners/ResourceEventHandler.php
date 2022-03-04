<?php

namespace App\Listeners;

use App\Events\ResourceSaved;
use Cerpus\EdlibResourceKit\Resource\ResourceManagerInterface;

class ResourceEventHandler
{
    public function __construct(private ResourceManagerInterface $resourceManager)
    {
    }

    public function handle(ResourceSaved $event): void
    {
        $this->resourceManager->save($event->getResource());
    }
}
