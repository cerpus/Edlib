<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Configuration\Locales;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class SetRequestLocale
{
    public function __construct(
        private Application $application,
        private Locales $locales,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->isStarted()) {
            $ltiLocale = $request->session()->get('lti.launch_presentation_locale');

            if ($ltiLocale !== null) {
                // inherit locale from LTI context
                $request->session()->put('locale', $ltiLocale);
            }

            $locale = $request->session()->get('locale');

            if ($locale !== null) {
                $this->application->setLocale(
                    $this->locales->getBestAvailable($locale),
                );
            }
        }

        return $next($request);
    }
}
