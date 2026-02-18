<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Configuration\Features;
use Closure;
use Illuminate\Contracts\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ShareFeaturesWithView
{
    public function __construct(
        private readonly Features $features,
        private readonly View\Factory $view,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->view->share('noindex', $this->features->isNoindexEnabled());

        return $next($request);
    }
}
