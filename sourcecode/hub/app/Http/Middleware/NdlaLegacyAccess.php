<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Configuration\NdlaLegacyConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class NdlaLegacyAccess
{
    public function __construct(private NdlaLegacyConfig $config) {}

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->config->isEnabled()) {
            throw new NotFoundHttpException('NDLA legacy endpoints are disabled');
        }

        return $next($request);
    }
}
