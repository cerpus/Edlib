<?php

if (env('DEPLOYMENT_ENVIRONMENT', '') != '') {

    // For deployed environments

    return [
        'default' => env('CACHE_DRIVER', 'memcached'),

        'stores' => [
            'memcached' => [
                'driver' => 'memcached',
                'options' => [
                    Memcached::OPT_TCP_NODELAY => true,
                    Memcached::OPT_NO_BLOCK => true,
                    Memcached::OPT_CONNECT_TIMEOUT => 2000,
                    Memcached::OPT_POLL_TIMEOUT => 2000,
                    Memcached::OPT_RECV_TIMEOUT => 750000,
                    Memcached::OPT_SEND_TIMEOUT => 750000,
                    Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
                    Memcached::OPT_RETRY_TIMEOUT => 2,
                    Memcached::OPT_SERVER_FAILURE_LIMIT => 1,
                    Memcached::OPT_AUTO_EJECT_HOSTS => true
                ],
                'servers' => [
                    ['host' => 'memcached-0', 'port' => 11211, 'weight' => 100],
                    ['host' => 'memcached-1', 'port' => 11211, 'weight' => 100],
                    ['host' => 'memcached-2', 'port' => 11211, 'weight' => 100],
                    ['host' => 'memcached-3', 'port' => 11211, 'weight' => 100],
                    ['host' => 'memcached-4', 'port' => 11211, 'weight' => 100]
                ],
            ],
            'redis' => [
                'driver' => 'redis',
                'connection' => 'default',
            ],
        ],

        'prefix' => env(
            'CACHE_PREFIX',
            'laravel_cache'
        ),
        'ttl' => [
            'assets' => env("CACHE_ASSETS_TTL", 259200),
        ]
    ];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    */

    'default' => env('CACHE_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */

    'stores' => [

        'apc' => [
            'driver' => 'apc',
        ],

        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'table'  => 'cache',
            'connection' => null,
        ],

        'file' => [
            'driver' => 'file',
            'path'   => storage_path('framework/cache'),
        ],

        'memcached' => [
            'driver'  => 'memcached',
            'servers' => [
                [
                    'host' => '127.0.0.1', 'port' => 11211, 'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    |
    */

    'prefix' => env(
        'CACHE_PREFIX',
        str_slug(env('APP_NAME', 'laravel'), '_').'_cache'
    ),

    'ttl' => [
        'assets' => env("CACHE_ASSETS_TTL", 3600),
    ]

];
