<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureFrameCookies;
use Illuminate\Http\Response;

final readonly class CookieController
{
    public function popup(): Response
    {
        return response()
            ->view('cookie.popup')
            ->withCookie(EnsureFrameCookies::createCookie());
    }
}
