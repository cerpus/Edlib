<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function abort;

/**
 * Verify that the user is authenticated via LTI.
 */
final readonly class LtiVerifyAuth
{
    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('authId')) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
