<?php

return [
    'adapters' => [
        'cerpus-metadata' => [
            'handler' => \Cerpus\MetadataServiceClient\Adapters\CerpusMetadataServiceAdapter::class,
            'base-url' => env('METADATA_SERVER'),
            'prefix' => env('METADATA_PREFIX', 'h5p-'),
        ],
    ],
];
