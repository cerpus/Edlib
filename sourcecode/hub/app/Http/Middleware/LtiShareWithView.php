<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Make LTI data available as a Blade variable.
 */
final readonly class LtiShareWithView
{
    public function __construct(private ViewFactory $viewFactory) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->viewFactory->share('lti', $request->session()->get('lti', []));

        return $next($request);
    }
}
