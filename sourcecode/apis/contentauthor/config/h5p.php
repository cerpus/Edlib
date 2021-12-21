<?php
return [
    'H5P_DragQuestion' => [
        'disableFullscreen' => env("H5P_DQ_DISABLE_FULLSCREEN", false),
    ],
    'H5P_Dialogcards' => [
        'useRichText' => env("H5P_DC_USE_RICH_TEXT", false),
    ],
    'storage' => [
        'publicPath' => env('CDN_WITH_PREFIX') ? '' : '/h5pstorage',
        'path' => env("UPLOAD_STORAGE_PATH_H5P", public_path() . '/h5pstorage'),
    ],
    'video' => [
        'enable' => env("H5P_VIDEO_STREAM_ENABLED", true),
        'adapter' => env("H5P_VIDEO_ADAPTER", 'streamps'), //deprecated
        'url' => env("H5P_VIDEO_URL"),
        'key' => env("H5P_VIDEO_ADAPTER_KEY"),
        'secret' => env("H5P_VIDEO_ADAPTER_SECRET"),
        'deleteVideoSourceAfterConvertToStream' => (bool)env("H5P_VIDEO_ADAPTER_DELETEVIDEO", true),
        'pingDelay' => env("H5P_VIDEO_DELAY", 10),
        'accountId' => env("H5P_VIDEO_ACCOUNT_ID"),
        'authUrl' => env("H5P_VIDEO_AUTH_URL"),
    ],
    'singleContentUpgrade' => env('H5P_SINGLE_CONTENT_UPGRADE', true),
    'developmentMode' => env('H5P_DEVELOPMENT_MODE', false),
    'isHubEnabled' => env('H5P_IS_HUB_ENABLED', false),
    'defaultExportOption' => env( 'H5P_DEFAULT_EXPORT_OPTION', H5PDisplayOptionBehaviour::CONTROLLED_BY_AUTHOR_DEFAULT_OFF),
    'include-mathjax' => env("H5P_INCLUDE_MATHJAX", true),
    'crossOrigin' => env('H5P_CROSSORIGIN'),
    'crossOriginRegexp' => env('H5P_CROSSORIGIN_REGEXP', '/.+/'),
    'overrideDisableSetting' => env("H5P_OVERRIDE_DISABLE_SETTING", false),
    'h5pAdapter' => env('H5P_ADAPTER', 'cerpus'),
    'image' => [
        'authDomain' => env("H5P_IMAGE_AUTH_DOMAIN"),
        'key' => env("H5P_IMAGE_AUTH_KEY"),
        'secret' => env("H5P_IMAGE_AUTH_SECRET"),
        'audience' => env("H5P_IMAGE_AUDIENCE"),
        'url' => env("H5P_IMAGE_URL"),
        'properties' => [
            'width' => env("H5P_IMAGE_PROPERTIES_WIDTH", 2500),
        ]
    ],
    'audio' => [
        'authDomain' => env("H5P_AUDIO_AUTH_DOMAIN"),
        'key' => env("H5P_AUDIO_AUTH_KEY"),
        'secret' => env("H5P_AUDIO_AUTH_SECRET"),
        'audience' => env("H5P_AUDIO_AUDIENCE"),
        'url' => env("H5P_AUDIO_URL"),
    ],
    'saveFrequency' => env('H5P_SAVE_FREQUENCY', 15),
    'include-custom-css' => env("H5P_INCLUDE_CUSTOM_CSS", false),
    'H5PStorageDisk' => env('H5P_STORAGE_DISK', 'h5p-uploads'),
    "default-resource-language" => env("H5P_DEFAULT_RESOURCE_LANGUAGE", "eng"),
    'upload-media-files-timeout' => env("H5P_UPLOAD_MEDIA_FILES_TIMEOUT", 5),
    'defaultShareSetting' => env("H5P_DEFAULT_SHARE_SETTING", 'private'),
    'showDisplayOptions' => env("H5P_SHOW_DISPLAY_OPTIONS", false),
];
