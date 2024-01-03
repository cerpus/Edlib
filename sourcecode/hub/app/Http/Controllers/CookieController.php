<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureFrameCookies;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Cookie;

final readonly class CookieController
{
    public function popup(): Response
    {
        $cookie = (new Cookie(EnsureFrameCookies::COOKIE_NAME))
            ->withValue('1')
            ->withSameSite(Cookie::SAMESITE_NONE)
            ->withSecure()
            ->withPartitioned();

        return response()
            ->view('cookie.popup')
            ->withCookie($cookie);
    }
}
