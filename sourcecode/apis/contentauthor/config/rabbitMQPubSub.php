<?php

use App\Messaging\Handlers\EdlibGdprDeleteRequest;

return [
    'connection' => [
        'secure' => trim(env('EDLIBCOMMON_RABBITMQ_SECURE', false)),
        'host' => trim(env('EDLIBCOMMON_RABBITMQ_HOST', 'localhost')),
        'port' => trim(env('EDLIBCOMMON_RABBITMQ_PORT', '5672')),
        'username' => trim(env('EDLIBCOMMON_RABBITMQ_USER', 'guest')),
        'password' => trim(env('EDLIBCOMMON_RABBITMQ_PASSWORD', 'guest')),
    ],
    'consumers' => [
        'edlib_gdpr_delete_request' => [
            'subscriptions' => [
                'edlib_gdpr_delete_request-contentauthor' => [
                    'handler' => EdlibGdprDeleteRequest::class
                ]
            ]
        ],
    ]
];
