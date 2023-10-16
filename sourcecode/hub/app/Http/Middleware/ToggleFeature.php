<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Configuration\Features;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class ToggleFeature
{
    public function __construct(private Features $features)
    {
    }

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!$this->features->enabled($feature)) {
            abort(Response::HTTP_NOT_FOUND, "The '$feature' feature is disabled");
        }

        return $next($request);
    }
}
