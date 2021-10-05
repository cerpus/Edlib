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

    'cloud' => env('FILESYSTEM_CLOUD', 'openstack'),

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

        'testDisk' => [
            'driver' => 'local',
            'root'   => base_path('tests'),
        ],

        'storageLogs' => [
            'driver' => 'local',
            'root'   => storage_path('logs'),
        ],

        'h5p-uploads' => [
            'driver' => env('UPLOAD_STORAGE_DRIVER', 'local'),
            'root' => env('UPLOAD_STORAGE_PATH_H5P', public_path() . '/h5pstorage'),
            'url' => env('UPLOAD_BASE_URL_H5P', '/h5pstorage'),
        ],

        'h5p' => [
            'driver' => env('LIBRARIES_H5P_STORAGE_DRIVER', 'local'),
            'root' => env('LIBRARIES_H5P_PATH', app_path() . '/Libraries/H5P')
        ],

        'article-uploads' => [
            'driver' => env('UPLOAD_STORAGE_DRIVER', 'local'),
            'root' => env('UPLOAD_STORAGE_PATH_ARTICLE', public_path() . '/h5pstorage/article-uploads'),
            'url' => '/h5pstorage/article-uploads',
        ],

        'game-uploads' => [
            'driver' => env('UPLOAD_STORAGE_DRIVER_GAME', 'local'),
            'root' => env('UPLOAD_STORAGE_PATH_GAME', public_path() . '/h5pstorage/games'),
            'url' => env('UPLOAD_BASE_URL_GAME', '/h5pstorage/games'),
        ],

        'game-uploads-s3' => [
            'driver' => env('UPLOAD_STORAGE_DRIVER_GAME', 's3'),
            'key' => env('S3_GAME_KEY', 's3-game-key'),
            'secret' => env('S3_GAME_SECRET', 's3-game-secret'),
            'region' => env('S3_GAME_REGION', 'eu-central-1'),
            'bucket' => env('S3_GAME_BUCKET', 'cerpus-ca-game-uploads'),
        ],

        'tmp' => [
            'driver' => 'local',
            'root' => '/tmp'
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => 'ftp.example.com',
            'username' => 'your-username',
            'password' => 'your-password',

            // Optional FTP Settings...
            // 'port'     => 21,
            // 'root'     => '',
            // 'passive'  => true,
            // 'ssl'      => true,
            // 'timeout'  => 30,
        ],

        's3' => [
            'driver' => 's3',
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

        'rackspace' => [
            'driver' => 'rackspace',
            'username' => 'your-username',
            'key' => 'your-key',
            'container' => 'your-container',
            'endpoint' => 'https://identity.api.rackspacecloud.com/v2.0/',
            'region' => 'IAD',
            'url_type' => 'publicURL',
        ],

        'openstack' => [
            'driver' => 'openstack',
            'container' => env('OPENSTACK_CONTAINER_NAME', 'ca_h5p'),
        ],
    ],

];
