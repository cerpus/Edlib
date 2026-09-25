<?php

declare(strict_types=1);

return [
    'sign-up' => (bool) env('FEATURE_SIGNUP_ENABLED', true),
    'forgot-password' => (bool) env('FEATURE_RESET_PASSWORD_ENABLED', true),
    'noindex' => (bool) env('FEATURE_NOINDEX', false),
    'social-users-are-verified' => (bool) env('FEATURE_SOCIAL_USERS_ARE_VERIFIED', false),
    // How to display the H5P Content type: 'h5p' to use content type machine name, 'h5p_title' to use content type title
    'ca-content-type-display' => env('FEATURE_CA_CONTENT_TYPE_DISPLAY', 'h5p'),
    // Polling interval for content lists (e.g. '5s', 5, or null/false/0/'none'/'disabled' to disable polling)
    'list-polling-interval' => env('FEATURE_LIST_POLLING_INTERVAL', '5s'),
    // Polling interval for content details (e.g. '5s', 5, or null/false/0/'none'/'disabled' to disable polling)
    'details-polling-interval' => env('FEATURE_DETAILS_POLLING_INTERVAL', '5s'),
];
