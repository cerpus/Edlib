<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectArrayLtiSignatures
{
    /**
     * Validates the format of the 'signature' parameter is not an array.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next The next middleware to process the request.
     * @return Response A JSON error response if the 'signature' is invalid, or the result of the next middleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (is_array($request->signature)) {
            return response()->json(['error' => 'Invalid signature format'], 400);
        }

        return $next($request);
    }
}
