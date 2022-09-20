<?php

namespace App\Http\Middleware;

use App\Oauth10\Oauth10Request;
use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SignedOauth10Request
{
    public function __construct(
        private string $consumerKey,
        private string $consumerSecret,
    ) {
    }

    /**
     * Handle an incoming API request.
     * Verify Oauth 1.0 signature
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next): mixed
    {
        $contentType = $request->header('Content-Type', '');
        if (str_starts_with($contentType, "application/x-www-form-urlencoded")) {
            $params = $request->all();
        } else {
            $params = $request->query?->all() ?? [];
        }
        $theRequest = new Oauth10Request(
            $request->getMethod(),
            $request->url(),
            $params,
            $request->header('Authorization', ''),
        );
        $validRequest = $theRequest->validateOauth10(
            $this->consumerKey,
            $this->consumerSecret,
        );
        if (!$validRequest) {
            throw new UnauthorizedHttpException(
                "Unable to verify signature of oAuth 1.0 request to " .
                $request->url(),
            );
        }

        return $next($request);
    }
}
