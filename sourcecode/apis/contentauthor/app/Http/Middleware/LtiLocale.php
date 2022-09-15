<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;
use Closure;
use Illuminate\Support\Facades\Session;
use App\H5pLti;

class LtiLocale
{
    public function __construct(private readonly H5pLti $h5pLti)
    {
    }

    /**
     * Set locale based on lti param
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        $ltiRequest = $this->h5pLti->getValidatedLtiRequest();
        if ($ltiRequest != null) {
            if ($ltiRequest->getLocale()) {
                Session::put('locale', $ltiRequest->getLocale());
            }
        }
        App::setLocale(Session::get('locale', config('app.fallback_locale')));

        return $next($request);
    }
}
