<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes sure a previous LTI launch has happened, and that the parameters from
 * it were stored in the session.
 */
final readonly class LtiSessionRequired
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !$request->hasPreviousSession() ||
            !$request->session()->has('lti')
        ) {
            abort(403);
        }

        return $next($request);
    }
}
