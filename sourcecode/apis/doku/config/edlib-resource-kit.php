<?php

return [

    // RabbitMQ configuration (required)
    'pub-sub' => [
        'host' => env('RABBITMQ_HOST', 'rabbitmq'),
        'port' => env('RABBITMQ_PORT', 5672),
        'username' => env('RABBITMQ_USERNAME', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'vhost' => env('RABBITMQ_VHOST', '/'),
        'secure' => env('RABBITMQ_SECURE', false),
        'ssl_options' => [],
    ],

    // To use a Cerpus\PubSub\PubSub instance from the container instead:
    //'pub-sub' => MyPubSubService::class,

    // By default, HTTP clients are discovered automatically
    'http-client' => null,

    'resource-serializer' => Cerpus\EdlibResourceKit\Serializer\ResourceSerializer::class,

];
