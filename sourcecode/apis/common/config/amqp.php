<?php

return [
    'connections' => [
        'rabbitmq' => [
            'connection' => [
                'class' => PhpAmqpLib\Connection\AMQPLazySSLConnection::class,
                'hosts' => [
                    [
                        'host' => env('EDLIBCOMMON_RABBITMQ_HOST', '127.0.0.1'),
                        'port' => env('EDLIBCOMMON_RABBITMQ_PORT', 5672),
                        'user' => env('EDLIBCOMMON_RABBITMQ_USER', 'guest'),
                        'password' => env('EDLIBCOMMON_RABBITMQ_PASSWORD', 'guest'),
                        'vhost' => env('RABBITMQ_VHOST', '/'),
                    ],
                ],
            ],

            'exchange' => [
                'type' => 'fanout',
                'durable' => true,
            ],
        ]
    ]
];
