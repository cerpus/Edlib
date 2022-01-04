<?php

return [
    "url" => env("EDLIBCOMMON_API_URL", "https://api.edlib.local"),
    "wwwUrl" => env("EDLIBCOMMON_WWW_URL", "https://www.edlib.local"),
    "embedPath" => env("EDLIBCOMMON_RESOURCE_EMBED_URL", "https://www.edlib.local/s/resources/<resourceId>"),
    "launchPath" => "/lti/v2/lti-links/",
    "apis" => [
        "auth" => [
            "url" => "http://authapi"
        ]
    ]
];
