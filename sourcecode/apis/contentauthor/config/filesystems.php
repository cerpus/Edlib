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
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

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
        'localBucket' => [ // used when running on docker compose setup locally
            'driver' => 'local',
            'root' => '/buckets/main_bucket',
        ],
        's3' => [ // used when running on AWS
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
        'tmp' => [ // temporary folder for contentauthor
            'driver' => 'local',
            'root' => '/tmp/contentauthor'
        ],
        'h5pTmp' => [ // temporary folder for h5p
            'driver' => 'local',
            'root' => '/tmp/h5p'
        ],
        'h5p-library' => [ // folder containing extra information about some h5p packages such as if they have max scores.
            'driver' => 'local',
            'root' => app_path() . '/Libraries/H5P'
        ],
    ],

];
