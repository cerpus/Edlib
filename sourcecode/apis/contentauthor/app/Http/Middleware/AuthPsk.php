<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Verify that a pre-shared key in request header field $headerField matches value in config $pskConfigName
class AuthPsk
{
    public function handle(Request $request, Closure $next, $headerField, $pskConfigName)
    {
        $reqKey = $request->header($headerField);
        $preKey = config($pskConfigName);

        if ($reqKey && $preKey && $reqKey === $preKey) {
            return $next($request);
        }

        return response("Unauthorized", Response::HTTP_UNAUTHORIZED);
    }
}
