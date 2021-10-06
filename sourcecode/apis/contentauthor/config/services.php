<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model'  => App\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'openstack' => [
        'username' => env('OPENSTACK_USERNAME'),
        'password' => env('OPENSTACK_KEY'),
        'authUrl' => env('OPENSTACK_AUTHURL'),
        'projectId' => env('OPENSTACK_PROJECT_ID'),
        'region'    => env('OPENSTACK_REGION_NAME', 'seria'),
        'domain'    => env('OPENSTACK_DOMAIN_NAME', 'default'),
    ],
    'nynorskroboten' => [
        'token' => env('NYNORSKROBOTEN_TOKEN'),
        'domain'    => env('NYNORSKROBOTEN_DOMAIN'),
    ],
];
