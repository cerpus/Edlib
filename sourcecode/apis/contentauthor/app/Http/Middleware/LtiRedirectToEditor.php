<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Lti\Lti;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect to the editor if launching LTI content with a Deep-Linking request.
 */
final readonly class LtiRedirectToEditor
{
    public function __construct(private Lti $lti) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->lti->getRequest($request)?->isContentItemSelectionRequest()) {
            return redirect($request->url() . '/edit' . '?redirectToken=' . $request->get('redirectToken'));
        }

        return $next($request);
    }
}
