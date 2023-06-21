<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use function redirect;
use function response;

/**
 * Checks for the 'Safari cookie problem', where cookies in iframes are blocked.
 * If applicable, the user is asked to open Edlib in a new window.
 */
final readonly class EnsureFrameCookies
{
    public const AFTER_REDIR_PARAM = '_edlib_cookie_check';
    public const COOKIE_NAME = '_edlib_cookies';

    /**
     * @param Closure(Request):Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hasCookie = $request->hasCookie(self::COOKIE_NAME);
        $redirected = $request->has(self::AFTER_REDIR_PARAM);

        if ($redirected && $hasCookie) {
            return redirect()->to(
                $request->fullUrlWithoutQuery(self::AFTER_REDIR_PARAM),
                Response::HTTP_TEMPORARY_REDIRECT,
            );
        }

        if ($redirected) {
            return response()->view('cookie.countermeasures', [
                'url' => $request->url(),
                'method' => $request->method(),
                'parameters' => $request->except(self::AFTER_REDIR_PARAM),
            ]);
        }

        if (!$hasCookie) {
            $cookie = (new Cookie(EnsureFrameCookies::COOKIE_NAME))
                ->withValue('1')
                ->withSameSite(Cookie::SAMESITE_NONE);

            return redirect()->to(
                $request->fullUrlWithQuery([self::AFTER_REDIR_PARAM => '1']),
                Response::HTTP_TEMPORARY_REDIRECT,
            )->withCookie($cookie);
        }

        return $next($request);
    }
}
