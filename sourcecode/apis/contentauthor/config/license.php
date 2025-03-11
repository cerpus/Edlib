<?php

return [
    'default-license' => env('LICENSE_DEFAULT', \App\Http\Libraries\License::LICENSE_EDLIB),
    'enabled' => env("ENABLE_LICENSING", true),
];
