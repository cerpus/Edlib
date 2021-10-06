<?php
return [
    'server' => env('LICENSE_SERVER', ''),
    'site' => env('LICENSE_SITE', ''),
    'default-license' => env('LICENSE_DEFAULT', \Cerpus\LicenseClient\Contracts\LicenseContract::LICENSE_EDLIB),
    'enabled' => env("ENABLE_LICENSING", true)
];
