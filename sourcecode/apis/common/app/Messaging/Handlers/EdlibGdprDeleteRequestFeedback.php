<?php

namespace App\Messaging\Handlers;

use Cerpus\LaravelRabbitMQPubSub\RabbitMQPubSubConsumerHandler;

class EdlibGdprDeleteRequestFeedback implements RabbitMQPubSubConsumerHandler
{
    public function consume(string $data)
    {
    }
}
