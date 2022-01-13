<?php
return [
    'oeruser' => env('OERUSER', env('NDLA_OERUSER', 'ndla')),
    'oerpass' => env('OERPASS', env('NDLA_OERPASS', 'ndla')),
    'baseUrl' => env('NDLA_BASEURL', 'http://ndlareplica.cerpusdev.net'),
    'linkBaseUrl' => 'https://ndla.no',
    'userId' => env('IMPORT_USERID', env('NDLA_IMPORT_USERID', 'fake-import-id')),
    'notifyCore' => env('NOTIFY_CORE', true),
    'edStepUrl' => env('EDSTEP_URL', false),
    'api' => [
        'uri' => env('NDLA_API_URI', 'https://api.ndla.no'),
        'userAgent' => env('NDLA_API_USER_AGENT', 'Cerpus NDLA Article API Client'),
        'pageSize' => env('NDLA_API_PAGE_SIZE', 15),
    ],
    'export' => [
        'collaborators' => env('NDLA_EXPORT_COLLABORATORS', ""),
    ]
];
