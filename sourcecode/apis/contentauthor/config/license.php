<?php
return [
    'server' => env('LICENSE_SERVER', 'http://licenseapi'),
    'site' => env('LICENSE_SITE', 'ContentAuthor'),
    'default-license' => env('LICENSE_DEFAULT', \Cerpus\LicenseClient\Contracts\LicenseContract::LICENSE_EDLIB),
    'enabled' => env("ENABLE_LICENSING", true)
];
