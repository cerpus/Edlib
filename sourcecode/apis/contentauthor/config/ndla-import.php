<?php
return [
    'video' => [
        'url' => env("NDLA_H5P_VIDEO_URL", 'https://cms.api.brightcove.com'),
        'key' => env("NDLA_H5P_VIDEO_ADAPTER_KEY", ''),
        'secret' => env("NDLA_H5P_VIDEO_ADAPTER_SECRET",''),
        'accountId' => env("NDLA_H5P_VIDEO_ACCOUNT_ID",'4806596774001'),
        'authUrl' => env("NDLA_H5P_VIDEO_AUTH_URL", 'https://oauth.brightcove.com/v4/access_token'),
    ],
];
