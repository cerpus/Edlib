<?php

namespace App\Http\Middleware;

use Closure;
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
        if (!in_array($role, $request->getSession()->get('roles', []), true)) {
            throw new UnauthorizedHttpException('User does not have necessary roles');
        }

        return $next($request);
    }
}
