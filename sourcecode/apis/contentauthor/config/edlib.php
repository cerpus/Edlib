<?php

return [
    "url" => env("EDLIBCOMMON_API_URL", "https://api.edlib.local"),
    "launchPath" => "/lti/v2/lti-links/",
    "apis" => [
        "auth" => [
            "url" => "http://authapi"
        ]
    ]
];
