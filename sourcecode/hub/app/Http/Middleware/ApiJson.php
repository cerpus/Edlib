<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Make error responses, etc. from the REST API be JSON unless HTML was
 * explicitly requested (as browsers do).
 *
 * This has to be a global middleware, or it won't apply to error pages.
 */
final readonly class ApiJson
{
    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->is('api', 'api/*') &&
            (clone $request)->prefers(['application/json', 'text/html']) === 'application/json'
        ) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
