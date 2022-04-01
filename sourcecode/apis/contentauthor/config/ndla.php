<?php
return [
    'userId' => env('IMPORT_USERID', env('NDLA_IMPORT_USERID', 'fake-import-id')),
    'notifyCore' => env('NOTIFY_CORE', true),
    'export' => [
        'collaborators' => env('NDLA_EXPORT_COLLABORATORS', ""),
    ]
];
