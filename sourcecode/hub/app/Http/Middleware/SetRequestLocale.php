<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class SetRequestLocale
{
    public function __construct(private Application $application)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->session()->isStarted() &&
            $request->session()->has('locale')
        ) {
            $this->application->setLocale($request->session()->get('locale'));
        }

        return $next($request);
    }
}
