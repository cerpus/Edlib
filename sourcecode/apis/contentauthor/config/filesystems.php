<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "s3"
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [

        'local' => [ // local app storage
            'driver' => 'local',
            'root' => storage_path('app'),
            'url' => env('APP_URL') . '/h5pstorage',
            'visibility' => 'public',
        ],

        's3' => [ // used when running on AWS
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL', env('CDN_WITH_PREFIX')),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'private',
        ],

        'testDisk' => [ // disk where tests are located.
            'driver' => 'local',
            'root'   => base_path('tests'),
        ],

        'storageLogs' => [ // disk where logs are stored
            'driver' => 'local',
            'root'   => storage_path('logs'),
        ],

        // temporary directory for test files
        // this has to be its own directory, or your files will go missing!
        'test' => [
            'driver' => 'local',
            'root' => '/tmp/contentauthor-test',
            'url' => 'http://localhost/h5pstorage/',
        ],

        'h5pTmp' => [ // temporary folder for h5p
            'driver' => 'local',
            'root' => '/tmp/h5p',
        ],

        'h5p-presave' => [
            'driver' => 'local',
            'root' => public_path('js/presave'),
            'url' => '/js/presave',
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('h5pstorage') => '../storage/app',
        public_path('h5p-php-library') => '../vendor/h5p/h5p-core',
        public_path('h5p-editor-php-library') => '../vendor/h5p/h5p-editor',
    ],

];
