<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Override;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/lti/content/*',
        '/lti/dl',
        '/lti/dl/tool/*/content/create',
        '/lti/dl/tool/*/content/*/update',
        '/lti/samples/deep-link',
    ];

    #[Override] protected function runningUnitTests(): false
    {
        // We don't want to exempt tests. This creates scenarios where tests may
        // pass, but the code is actually broken.
        return false;
    }
}
