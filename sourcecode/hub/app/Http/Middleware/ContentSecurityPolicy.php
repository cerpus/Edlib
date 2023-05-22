<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
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
    ) {
    }

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->vite->useCspNonce();

        $response = $next($request);

        if (!self::isCspEnabled($request, $response)) {
            return $response;
        }

        $frameSources = $request->attributes->get('csp_frame_src', ["'none'"]);
        assert(is_array($frameSources));

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
                "frame-src " . implode(' ', $frameSources) . "; " .
                "script-src 'nonce-" . $this->vite->cspNonce() . "'; " .
                "style-src 'nonce-" . $this->vite->cspNonce() . "'",
        );

        return $response;
    }

    public static function allowFrame(Request $request, string $source): void
    {
        $sources = $request->attributes->get('csp_frame_src', []);
        assert(is_array($sources));
        $sources[] = $source;

        $request->attributes->set('csp_frame_src', $sources);
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
