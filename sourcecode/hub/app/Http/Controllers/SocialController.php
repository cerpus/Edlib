<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

final readonly class SocialController
{
    public function login(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $credentials = Socialite::driver($provider)->user();
        $user = User::fromSocial($provider, $credentials);

        Auth::login($user);

        return redirect()->route('home');
    }
}
