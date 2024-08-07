<?php

return [
    'feature' => [
        'licensing' => env('NDLA_FEATURE_LICENSING', false),
        'content-locking' => env('NDLA_FEATURE_CONTENT_LOCKING', true),
        'context-collaboration' => env('NDLA_FEATURE_CONTEXT_COLLABORATION', false),
        'collaboration' => env('NDLA_FEATURE_COLLABORATION', false),
        'export_h5p_on_save' => env('NDLA_FEATURE_EXPORT_H5P_ON_SAVE', false),
        "export_h5p_with_local_files" => env("NLDA_FEATURE_EXPORT_H5P_WITH_LOCAL_FILES", false),
    ],
    'app' => [
        'enable_licensing' => env('NDLA_ENABLE_LICENSING', false),
        'displayPropertiesBox' => env('NDLA_H5P_DISPLAY_PROPERTIES_BOX', true),
    ],
    'ndla' => [
        'userId'  => env('NDLA_IMPORT_USERID', 'fake-import-id'),
    ],
    'h5p' => [
        'H5P_DragQuestion' => [
            'disableFullscreen' => env("NDLA_H5P_DQ_DISABLE_FULLSCREEN", false),
        ],
        'H5P_Dialogcards' => [
            'useRichText' => env("NDLA_H5P_DC_USE_RICH_TEXT", false),
        ],
        'singleContentUpgrade' => env('NDLA_H5P_SINGLE_CONTENT_UPGRADE', true),
        'isHubEnabled' => env('NDLA_H5P_IS_HUB_ENABLED', false),
        'defaultExportOption' => env('NDLA_H5P_DEFAULT_EXPORT_OPTION', H5PDisplayOptionBehaviour::CONTROLLED_BY_AUTHOR_DEFAULT_OFF),
        'include-mathjax' => env("NDLA_H5P_INCLUDE_MATHJAX", true),
        'crossOrigin' => env('NDLA_H5P_CROSSORIGIN'),
        'crossOriginRegexp' => env('NDLA_H5P_CROSSORIGIN_REGEXP', '/.*/'),
        'overrideDisableSetting' => env("NDLA_H5P_OVERRIDE_DISABLE_SETTING", false),
        'h5pAdapter' => 'ndla',
        'image' => [
            'url' => env("NDLA_H5P_IMAGE_URL"),
        ],
        'audio' => [
            'url' => env("NDLA_H5P_AUDIO_URL"),
        ],
        'video' => [
            'enable' => env("NDLA_H5P_VIDEO_STREAM_ENABLED", true),
            'url' => env("NDLA_H5P_VIDEO_URL"),
            'key' => env("NDLA_H5P_VIDEO_ADAPTER_KEY"),
            'secret' => env("NDLA_H5P_VIDEO_ADAPTER_SECRET"),
            'deleteVideoSourceAfterConvertToStream' => (bool)env("NDLA_H5P_VIDEO_ADAPTER_DELETEVIDEO", false),
            'pingDelay' => env("NDLA_H5P_VIDEO_DELAY", 10),
            'accountId' => env("NDLA_H5P_VIDEO_ACCOUNT_ID"),
            'authUrl' => env("NDLA_H5P_VIDEO_AUTH_URL"),
        ],
        'saveFrequency' => env('NDLA_H5P_SAVE_FREQUENCY', false),
    ]
];
