<?php

declare(strict_types=1);

return [
    'sign-up' => (bool) env('FEATURE_SIGNUP_ENABLED', true),

    'forgot-password' => (bool) env('FEATURE_RESET_PASSWORD_ENABLED', true),
];
