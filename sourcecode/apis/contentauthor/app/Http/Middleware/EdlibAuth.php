<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EdlibAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (Session::get('user') == null && !in_array($role, Session::get('roles', []))) {
            throw new UnauthorizedHttpException('User does not have necessary roles');
        }

        return $next($request);
    }
}
