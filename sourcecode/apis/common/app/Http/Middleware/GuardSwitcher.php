<?php

namespace App\Http\Middleware;

class GuardSwitcher {
    public function handle($request, \Closure $next, $defaultGuard = null) {
        if (isset(config("auth.guards")[$defaultGuard])) {
            config(["auth.defaults.guard" => $defaultGuard]);
        }

        return $next($request);
    }
}
