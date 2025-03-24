<?php

return [
    'H5P_DragQuestion' => [
        'disableFullscreen' => env("H5P_DQ_DISABLE_FULLSCREEN", false),
    ],
    'H5P_Dialogcards' => [
        'useRichText' => env("H5P_DC_USE_RICH_TEXT", false),
    ],
    'storage' => [
        'publicPath' => '',
        'path' => env("UPLOAD_STORAGE_PATH_H5P", public_path() . '/h5pstorage'),
    ],
    'audio' => [
        'adapter' => env('H5P_AUDIO_ADAPTER'),
    ],
    'image' => [
        'adapter' => env('H5P_IMAGE_ADAPTER'),
    ],
    'video' => [
        'adapter' => env('H5P_VIDEO_ADAPTER'),
        'enable' => env("H5P_VIDEO_STREAM_ENABLED", true),
        'deleteVideoSourceAfterConvertToStream' => (bool) env("H5P_VIDEO_ADAPTER_DELETEVIDEO", true),
        'pingDelay' => env("H5P_VIDEO_DELAY", 10),
    ],
    'singleContentUpgrade' => env('H5P_SINGLE_CONTENT_UPGRADE', true),
    'developmentMode' => env('H5P_DEVELOPMENT_MODE', false),
    'isHubEnabled' => env('H5P_IS_HUB_ENABLED', false),
    'defaultExportOption' => env('H5P_DEFAULT_EXPORT_OPTION', H5PDisplayOptionBehaviour::CONTROLLED_BY_AUTHOR_DEFAULT_OFF),
    'include-mathjax' => env("H5P_INCLUDE_MATHJAX", true),
    'crossOrigin' => env('H5P_CROSSORIGIN'),
    'crossOriginRegexp' => env('H5P_CROSSORIGIN_REGEXP', '/.+/'),
    'overrideDisableSetting' => env("H5P_OVERRIDE_DISABLE_SETTING", false),
    'h5pAdapter' => env('H5P_ADAPTER', 'cerpus'),
    'saveFrequency' => env('H5P_SAVE_FREQUENCY', 15),
    'include-custom-css' => env("H5P_INCLUDE_CUSTOM_CSS", false),
    "default-resource-language" => env("H5P_DEFAULT_RESOURCE_LANGUAGE", "eng"),
    'upload-media-files-timeout' => env("H5P_UPLOAD_MEDIA_FILES_TIMEOUT", 5),
    'showDisplayOptions' => env("H5P_SHOW_DISPLAY_OPTIONS", false),

    // one of 'null', 'nynorskroboten', 'nynorobot'
    'translator' => env('H5P_TRANSLATOR', env('H5P_NYNORSK_ADAPTER', 'null')),
    'ckeditor' => [
        'textPartLanguages' => env("H5P_CKEDITOR_TEXT_PART_LANGUAGES", 'en,nb,nn'),
    ],
];
