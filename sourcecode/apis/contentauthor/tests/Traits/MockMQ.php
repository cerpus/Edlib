<?php

namespace Tests\Traits;

use App\Listeners\ResourceEventSubscriber;

trait MockMQ
{

    public function setUpMockMQ()
    {
        $partialMock = $this->createPartialMock(ResourceEventSubscriber::class, ['onResourceSaved']);
        app()->instance(ResourceEventSubscriber::class, $partialMock);
    }

}
