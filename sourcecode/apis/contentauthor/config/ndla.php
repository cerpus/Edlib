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
        'cdnUrl' => env('NDLA_H5P_IMAGE_CDN_URL', 'https://images.ndla.no'),
        'properties' => [
            'width' => env('NDLA_H5P_IMAGE_PROPERTIES_WIDTH', 2500),
        ],
        'searchparams' => [
            'fallback' => env('NDLA_H5P_IMAGE_SEARCH_FALLBACK', true),
            'license' => env('NDLA_H5P_IMAGE_SEARCH_LICENSE', 'all'),
            'pagesize' => env('NDLA_H5P_IMAGE_SEARCH_PAGESIZE', 15),
        ],
        'modifyDomainPaths' => array_values(
            array_unique(
                array_filter(
                    array_map('trim', explode(',', env('NDLA_H5P_IMAGE_MODIFY_DOMAIN_PATHS', env('NDLA_H5P_IMAGE_URL'))))
                )
            )
        ),
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
