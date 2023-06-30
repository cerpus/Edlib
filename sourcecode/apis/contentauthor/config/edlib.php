<?php

return [
    "wwwUrl" => env("EDLIBCOMMON_WWW_URL", 'https://www.' . env('EDLIB_ROOT_DOMAIN', 'edlib.test')),
    "embedPath" => env("EDLIBCOMMON_RESOURCE_EMBED_URL", 'https://www.' . env('EDLIB_ROOT_DOMAIN', 'edlib.test'). '/s/resources/<resourceId>'),
    "apis" => [
        "auth" => [
            "url" => "http://authapi"
        ]
    ]
];
