<?php

return [
    'versionserver' => env('VERSION_API_SERVER', 'http://versionapi'),
    'oauthkey' => env('VERSION_OAUTH_KEY', 'VersionNoDefaultKey'),
    'oauthsecret' => env('VERSION_OAUTH_SECRET', 'VersionNoDefaultSecret'),
    'system_name' => env('VERSION_SYSTEM_NAME', 'ContentAuthor'),
];
