<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function is_string;

/**
 * Sets a locale based on the query string. The accepted format is BCP-47.
 */
final readonly class SetsLocaleFromQuery
{
    public function __construct(private Application $application) {}

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('locale');

        if (
            is_string($locale) &&
            // Check that we have a 2-3 letter language tag, optional 4 letter
            // script identifier, and optional 2-3 character region specifier.
            preg_match('/\A[A-Za-z]{2,3}(?:-[A-Za-z]{4})?(?:-[A-Za-z0-9]{2,3})?\z/', $locale)
        ) {
            $this->application->setLocale($locale);
        }

        return $next($request);
    }
}
