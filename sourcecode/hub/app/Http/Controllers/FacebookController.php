<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookController extends Controller
{
    public function loginWithFacebook(): RedirectResponse
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function callbackFromFacebook(): RedirectResponse
    {
        $facebookUser = Socialite::driver('facebook')->user();

        $user = User::updateOrCreate(
            ['email' => $facebookUser->getEmail()],
            ['name' => $facebookUser->getName(), 'facebook_id' => $facebookUser->getId()]
        );

        Auth::login($user);

        return redirect()->route('home');
    }
}
