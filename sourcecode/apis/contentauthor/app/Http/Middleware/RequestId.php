<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RequestId
{
    /**
     * Request ID
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headerName = 'X-Request-Id';
        $requestId = $request->header($headerName, Uuid::uuid4()->toString());

        app()->singleton('requestId', function ($app) use ($requestId) {
            return $requestId;
        });

        /** @var Response|BinaryFileResponse $response */
        $response = $next($request);

        if( method_exists($response, "header")){
            $response->header($headerName, $requestId);
        }

        return $response;
    }
}
