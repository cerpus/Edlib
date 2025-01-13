<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\SessionScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Substitute the session for the duration of a request for another one,
 * identified by a token in a query string.
 *
 * This is used to avoid the global session state being affected by LTI
 * launches. To be effective, all URLs generated must contain the query string.
 *
 * To prevent anyone with the query param from being authenticated, the true
 * session ID is stored in the "outer" session, and the token in the query param
 * identifies which of these to use.
 */
final readonly class ScopedSession
{
    public function __construct(private SessionScope $scope) {}

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->scope->resume($request);

        try {
            return $next($request);
        } finally {
            $this->scope->restore($request);
        }
    }
}
