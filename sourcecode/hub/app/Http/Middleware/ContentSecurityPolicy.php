<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function app;
use function is_array;
use function str_starts_with;

final class ContentSecurityPolicy
{
    public function __construct(
        private readonly Vite $vite,
        private readonly UrlGenerator $urlGenerator,
    ) {
    }

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!self::isCspEnabled($request, $response)) {
            return $response;
        }

        $default = "'self' " . $this->urlGenerator->asset('');

        if ($this->vite->isRunningHot()) {
            $viteBaseUrl = $this->vite->asset('');
            $viteWssBaseUrl = preg_replace('!^https?://!', 'wss://', $viteBaseUrl);

            $default .= " $viteBaseUrl $viteWssBaseUrl";
        }

        $response->headers->set(
            'Content-Security-Policy',
            "default-src $default" .
                "; img-src $default data:" .
                "; script-src 'nonce-" . $this->vite->cspNonce() . "'" .
                "; style-src 'nonce-" . $this->vite->cspNonce() . "'",
        );

        return $response;
    }

    public static function isCspEnabled(Request $request, Response $response): bool
    {
        if (!app()->isLocal()) {
            return true;
        }

        // TODO: we can probably detect these better.

        // Exempt error pages
        if (!$response->isSuccessful()) {
            return false;
        }

        // Exempt telescope
        if (str_starts_with($request->path(), 'telescope')) {
            return false;
        }

        return true;
    }
}
