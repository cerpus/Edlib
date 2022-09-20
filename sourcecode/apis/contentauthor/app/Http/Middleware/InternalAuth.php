<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InternalAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header("x-api-key");

        if ($apiKey !== null && config('internal.key') === $apiKey) {
            return $next($request);
        }

        return response("Unauthorized", ResponseAlias::HTTP_UNAUTHORIZED);
    }
}
