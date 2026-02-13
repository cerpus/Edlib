<?php

declare(strict_types=1);

return [
    'sign-up' => (bool) env('FEATURE_SIGNUP_ENABLED', true),
    'forgot-password' => (bool) env('FEATURE_RESET_PASSWORD_ENABLED', true),
    'noindex' => (bool) env('FEATURE_NOINDEX', false),
    'social-users-are-verified' => (bool) env('FEATURE_SOCIAL_USERS_ARE_VERIFIED', false),
    // How to display the H5P Content type: 'h5p' to use content type machine name, 'h5p_title' to use content type title
    'ca-content-type-display' => env('FEATURE_CA_CONTENT_TYPE_DISPLAY', 'h5p'),
];
