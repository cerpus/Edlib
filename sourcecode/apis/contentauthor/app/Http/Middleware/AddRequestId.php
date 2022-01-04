<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;
use Closure;

class AddRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $request->request->add(['CA_RequestId' => uniqid()]);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': Could not add CA_RequestId to request.');
        }

        return $next($request);
    }
}
