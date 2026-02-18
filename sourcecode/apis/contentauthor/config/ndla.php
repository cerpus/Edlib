<?php

return [
    'export' => [
        'collaborators' => env('NDLA_EXPORT_COLLABORATORS', ""),
    ],

    'video' => [
        'url' => env('NDLA_H5P_VIDEO_URL'),
        'key' => env("NDLA_H5P_VIDEO_ADAPTER_KEY"),
        'secret' => env("NDLA_H5P_VIDEO_ADAPTER_SECRET"),
        'accountId' => env("NDLA_H5P_VIDEO_ACCOUNT_ID"),
        'authUrl' => env("NDLA_H5P_VIDEO_AUTH_URL"),
    ],

    'image' => [
        'url' => env('NDLA_H5P_IMAGE_URL'),
        'properties' => [
            'width' => env('NDLA_H5P_IMAGE_PROPERTIES_WIDTH', 2500),
        ],
        'searchparams' => [
            'fallback' => env('NDLA_H5P_IMAGE_SEARCH_FALLBACK', true),
            'license' => env('NDLA_H5P_IMAGE_SEARCH_LICENSE', 'all'),
            'pagesize' => env('NDLA_H5P_IMAGE_SEARCH_PAGESIZE', 15),
        ],
    ],

    'audio' => [
        'url' => env('NDLA_H5P_AUDIO_URL'),
        'searchparams' => [
            'fallback' => env('NDLA_H5P_AUDIO_SEARCH_FALLBACK', true),
            'license' => env('NDLA_H5P_AUDIO_SEARCH_LICENSE', 'all'),
            'pagesize' => env('NDLA_H5P_AUDIO_SEARCH_PAGESIZE', 10),
        ],
    ],

];
