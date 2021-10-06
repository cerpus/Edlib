<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InternalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
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
