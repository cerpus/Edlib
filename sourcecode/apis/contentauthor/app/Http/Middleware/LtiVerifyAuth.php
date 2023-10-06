<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verify that the user is authenticated via LTI.
 */
class LtiVerifyAuth
{
    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::get('authId')) {
            return $next($request);
        }

        return redirect('auth/login');
    }
}
