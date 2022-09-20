<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Session;
use Closure;
use Illuminate\Http\Response;

class APIAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('authId')) {
            return $next($request);
        }

        return response("Unauthorized", Response::HTTP_UNAUTHORIZED);
    }
}
