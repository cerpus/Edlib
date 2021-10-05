<?php

return [
    //default adapter for questionsets
    "default" => "imageservice",

    "adapters" => [

        "imageservice" => [
            "handler" => \Cerpus\ImageServiceClient\Adapters\ImageServiceAdapter::class,
            "base-url" => env('IMAGESERVICE_URL'),
            "system-name" => env('IMAGESERVICE_SYSTEM_NAME', env('VERSION_SYSTEM_NAME', 'ContentAuthor')),
        ],

    ],
];
