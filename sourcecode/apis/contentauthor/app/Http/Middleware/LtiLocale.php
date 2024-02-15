<?php

namespace App\Http\Middleware;

use App\Lti\Lti;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Iso639p3;

class LtiLocale
{
    public function __construct(private readonly Lti $lti)
    {
    }

    /**
     * Set locale based on lti param
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        $ltiRequest = $this->lti->getRequest($request);
        if ($ltiRequest != null) {
            if ($ltiRequest->getLocale()) {
                // Store the original code, even if we don't have this locale, maybe H5P does
                Session::put('locale', $ltiRequest->getLocale());
            }
        }
        App::setLocale($this->resolveLocale(Session::get('locale', config('app.fallback_locale'))));

        return $next($request);
    }

    /**
     * Check if we have a translation for the code. If that failes, and a code longer than two characters is
     * given, check if we have a translation for the two-code version. Failing that the original code is returned.
     */
    private function resolveLocale(string $locale): string
    {
        if (!file_exists(resource_path('lang/' . $locale)) && strlen($locale) > 2) {
            $lang = Iso639p3::code2letters($locale);
            if (file_exists(resource_path('lang/' . $lang))) {
                return $lang;
            }
        }

        return $locale;
    }
}
