<?php

use App\Messaging\Handlers\AuthMigrationExecute;
use App\Messaging\Handlers\AuthMigrationGetFeedback;
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
        'auth_migration_get_info' => [
            'subscriptions' => [
                'auth_migration_get_info-contentauthor' => [
                    'handler' => AuthMigrationGetFeedback::class
                ]
            ]
        ],
        'auth_migration_execute' => [
            'subscriptions' => [
                'auth_migration_execute-contentauthor' => [
                    'handler' => AuthMigrationExecute::class
                ]
            ]
        ],
    ]
];
