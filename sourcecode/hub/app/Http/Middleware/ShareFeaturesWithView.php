<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Configuration\Features;
use Closure;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class ShareFeaturesWithView
{
    public function __construct(
        private ViewFactory $viewFactory,
        private Features $features,
    ) {
    }

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->viewFactory->share('features', $this->features);

        return $next($request);
    }
}
