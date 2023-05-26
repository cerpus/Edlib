<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleController extends Controller
{
    public function loginWithGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackFromGoogle(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            ['name' => $googleUser->getName(), 'google_id' => $googleUser->getId()]
        );

        Auth::login($user);

        return redirect()->route('home');
    }
}
