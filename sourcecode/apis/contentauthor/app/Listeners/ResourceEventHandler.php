<?php

namespace App\Listeners;

use App\Events\ResourceSaved;
use Cerpus\EdlibResourceKit\Resource\ResourceManagerInterface;

class ResourceEventHandler
{
    public function __construct(
        private ResourceManagerInterface $resourceManager,
        private readonly bool $enableEdlib2,
    ) {
    }

    public function handle(ResourceSaved $event): void
    {
        if ($this->enableEdlib2) {
            $this->resourceManager->save($event->getResource());
        }
    }
}
