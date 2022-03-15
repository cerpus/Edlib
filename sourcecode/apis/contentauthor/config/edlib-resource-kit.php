<?php

return [

    //
    // If false, resources will be published asynchronously over the message
    // bus. This is fast, but you get no feedback in the event that publishing
    // was unsuccessful.
    //
    // If true, resources will be synchronously published over HTTP. This is
    // slower, but allows you to handle errors while publishing.
    //
    'synchronous-resource-manager' => false,

    //
    // RabbitMQ configuration
    // (only required if 'synchronous-resource-manager' => false):
    //
    'pub-sub' => Cerpus\PubSub\Connection\ConnectionFactory::class,

    'resource-serializer' => App\EdlibResource\ResourceSerializer::class,

];
