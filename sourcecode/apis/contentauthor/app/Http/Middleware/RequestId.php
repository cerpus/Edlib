<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Log\Logger;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a unique request ID to request & response headers and log context.
 */
class RequestId
{
    private const HEADER_NAME = 'X-Request-Id';

    public function __construct(private readonly Logger $logger) {}

    /**
     * @param  \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next): mixed
    {
        $requestId = Uuid::uuid4();
        $request->headers->set(self::HEADER_NAME, $requestId);

        $this->logger->withContext(['requestId' => $requestId]);

        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set(self::HEADER_NAME, $requestId);
        }

        return $response;
    }
}
