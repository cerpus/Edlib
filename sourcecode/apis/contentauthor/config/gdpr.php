<?php
return [
    'deletion-class' => App\Gdpr\Deletion::class,
    'queue-driver' => env('GDPR_QUEUE_DRIVER', env('QUEUE_DRIVER', 'sync')),
    'queue' => env('GDPR_QUEUE', 'default'),
];
