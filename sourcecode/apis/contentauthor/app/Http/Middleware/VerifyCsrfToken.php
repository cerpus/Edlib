<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     */
    protected $except = [
        'ajax',
        'api/progress',
        'admin/content/upgrade*',
        'content/upgrade',
        'lti-content/*',
        'h5p/create',
        'h5p/*/edit',
        'h5p/*/upgrade-content',
        'h5p/*',
        "create",
        "create/*",
        'article/*',
        'link/*',
        'questionset/*',
        'admin/capabilities/*/*',
        'v1/copy',
        'api/v1/contenttypes/*',
        'api/v1/resources/*/publish',
        'api/v1/h5p/import',
        'game/*',
        "v1/content/*/unlock", // The unlock endpoint now responds to both GET and POST methods
        'lti/admin',
    ];
}
