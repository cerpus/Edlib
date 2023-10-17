<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Verify that a signed LTI launch happened.
 */
final readonly class LtiSignedLaunch
{
    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->session()->get('signedLaunch') ||
            // For backwards compat.
            // Cannot be set without a signed launch also having occurred.
            $request->session()->get('authId')
        ) {
            return $next($request);
        }

        throw new UnauthorizedHttpException(
            challenge: 'OAuth',
            message: 'A valid LTI launch is required to have occurred',
        );
    }
}
