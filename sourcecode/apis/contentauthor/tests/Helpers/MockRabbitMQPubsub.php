<?php

namespace Tests\Helpers;

use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSub;

trait MockRabbitMQPubsub
{
    public function setupMockRabbitMQPubsub(): void
    {
        $rabbitMQMock = $this->getMockBuilder(RabbitMQPubSub::class)->disableOriginalConstructor()->getMock();
        $this->instance(RabbitMQPubSub::class, $rabbitMQMock);
    }
}
