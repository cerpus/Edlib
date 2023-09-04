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
    | Supported: "local", "ftp", "s3", "rackspace"
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
        ],
        'testDisk' => [ // disk where tests are located.
            'driver' => 'local',
            'root'   => base_path('tests'),
        ],
        'storageLogs' => [ // disk where logs are stored
            'driver' => 'local',
            'root'   => storage_path('logs'),
        ],
        's3' => [ // used when running on AWS
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('CDN_WITH_PREFIX'),
        ],
        // temporary directory for test files
        // this has to be its own directory, or your files will go missing!
        'test' => [
            'driver' => 'local',
            'root' => '/tmp/contentauthor-test',
        ],
        'tmp' => [ // temporary folder for contentauthor
            'driver' => 'local',
            'root' => '/tmp/contentauthor'
        ],
        'h5pTmp' => [ // temporary folder for h5p
            'driver' => 'local',
            'root' => '/tmp/h5p'
        ],
        'h5p-presave' => [
            'driver' => 'local',
            'root' => public_path('js/presave'),
            'url' => '/js/presave',
        ],
    ],

];
