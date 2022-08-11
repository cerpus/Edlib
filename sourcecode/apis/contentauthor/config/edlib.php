<?php

return [
    "wwwUrl" => env("EDLIBCOMMON_WWW_URL", "https://www.edlib.local"),
    "embedPath" => env("EDLIBCOMMON_RESOURCE_EMBED_URL", "https://www.edlib.local/s/resources/<resourceId>"),
    "apis" => [
        "auth" => [
            "url" => "http://authapi"
        ]
    ]
];
