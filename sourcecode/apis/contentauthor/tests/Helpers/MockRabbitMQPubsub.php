<?php

namespace Tests\Helpers;

use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSub;

trait MockRabbitMQPubsub
{
    public function setupRabbitMQPubSub()
    {
        $rabbitMQMock = $this->getMockBuilder(RabbitMQPubSub::class)->disableOriginalConstructor()->getMock();
        $this->instance(RabbitMQPubSub::class, $rabbitMQMock);
    }
}
