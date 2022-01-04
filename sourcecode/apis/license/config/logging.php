<?php

use App\LogstashLogger;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\SocketHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
    'default' => env('LOG_CHANNEL', env('DEPLOYMENT_ENVIRONMENT', '') != '' ? 'deployed-stack' : 'single'),

    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],
        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'deployed-stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'logstash']
        ],
        'logstash' => [
            'driver' => 'custom',
            'level' => 'debug',
            'via' => function($config) {
                $handler = new SocketHandler("udp://{$config['host']}:{$config['port']}");
                $handler->setChunkSize(65530);
                $handler->setFormatter(new LogstashFormatter($config['appName']));

                return new Logger('logstash.main', [$handler]);
            },
            'host' => env('LOGSTASH_HOST', 'logstash.elk'),
            'port' => env('LOGSTASH_PORT', 9605),
            'appName' => 'licenseapi.' . env('POD_NAMESPACE', 'default')
        ]
    ],

];
