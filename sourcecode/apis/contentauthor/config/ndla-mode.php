<?php
return [
    'feature' => [
        'licensing' => env('NDLA_FEATURE_LICENSING', false),
        'content-locking' => env('NDLA_FEATURE_CONTENT_LOCKING', true),
        'context-collaboration' => env('NDLA_FEATURE_CONTEXT_COLLABORATION', false),
        'no-rabbitmq' => env('NDLA_FEATURE_NO_RABBITMQ', false),
        'collaboration' => env('NDLA_FEATURE_COLLABORATION', false),
        'enableDraftLogic' => env('NDLA_FEATURE_ENABLE_DRAFT_LOGIC', false),
        'export_h5p_on_save' => env('NDLA_FEATURE_EXPORT_H5P_ON_SAVE', false),
        "export_h5p_with_local_files" => env("NLDA_FEATURE_EXPORT_H5P_WITH_LOCAL_FILES", false),
    ],
    'app' => [
        'enable_licensing' => env('NDLA_ENABLE_LICENSING', false),
        'enable_ndla_import' => env('NDLA_ENABLE_NDLA_IMPORT', false),
        'displayPropertiesBox' => env('NDLA_H5P_DISPLAY_PROPERTIES_BOX', true),
    ],
    'ndla' => [
        'oeruser' => env('NDLA_OERUSER', 'ndla'),
        'oerpass' => env('NDLA_OERPASS', 'ndla'),
        'baseUrl' => env('NDLA_BASEURL', 'http://ndla.no'),
        'linkBaseUrl' => env('NDLA_LINK_BASE_URL', 'Please set the NDLA_LINK_BASE_URL env variable'),
        'userId'  => env('NDLA_IMPORT_USERID', 'fake-import-id'),
        'notifyCore' => env('NDLA_NOTIFY_CORE', false),
    ],
    'h5p' => [
        'H5P_DragQuestion' => [
            'disableFullscreen' => env("NDLA_H5P_DQ_DISABLE_FULLSCREEN", false),
        ],
        'H5P_Dialogcards' => [
            'useRichText' => env("NDLA_H5P_DC_USE_RICH_TEXT", false),
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
        'singleContentUpgrade' => env('NDLA_H5P_SINGLE_CONTENT_UPGRADE', true),
        'isHubEnabled' => env('NDLA_H5P_IS_HUB_ENABLED', false),
        'defaultExportOption' => env( 'NDLA_H5P_DEFAULT_EXPORT_OPTION', H5PDisplayOptionBehaviour::CONTROLLED_BY_AUTHOR_DEFAULT_OFF),
        'include-mathjax' => env("NDLA_H5P_INCLUDE_MATHJAX", true),
        'crossOrigin' => env('NDLA_H5P_CROSSORIGIN'),
        'crossOriginRegexp' => env('NDLA_H5P_CROSSORIGIN_REGEXP', '/.*/'),
        'overrideDisableSetting' => env("NDLA_H5P_OVERRIDE_DISABLE_SETTING", false),
        'h5pAdapter' => 'ndla',
        'image' => [
            'authDomain' => env("NDLA_H5P_IMAGE_AUTH_DOMAIN"),
            'key' => env("NDLA_H5P_IMAGE_AUTH_KEY"),
            'secret' => env("NDLA_H5P_IMAGE_AUTH_SECRET"),
            'audience' => env("NDLA_H5P_IMAGE_AUDIENCE"),
            'url' => env("NDLA_H5P_IMAGE_URL"),
        ],
        'saveFrequency' => env('NDLA_H5P_SAVE_FREQUENCY', false),
    ],
    'metadata' => [
        'published-field' => env('NDLA_METADATA_PUBLISHED_FIELD', false)
    ]
];
