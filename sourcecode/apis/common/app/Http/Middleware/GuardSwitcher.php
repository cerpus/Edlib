<?php

namespace App\Http\Middleware;

class GuardSwitcher {
    public function handle($request, \Closure $next, $defaultGuard = null) {
        if (in_array($defaultGuard, array_keys(config("auth.guards")))) {
            config(["auth.defaults.guard" => $defaultGuard]);
        }

        return $next($request);
    }
}
