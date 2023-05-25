<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function loginWithGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackFromGoogle()
    {
        try {
            $user = Socialite::driver('google')->user();

            // Check if user already exists
            $existingUser = User::where('email', $user->getEmail())->first();

            if (!$existingUser) {
                // Create new user
                $newUser = User::create([
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'password' => Hash::make($user->getName() . '@' . $user->getId())
                ]);

                Auth::login($newUser);
            } else {
                Auth::login($existingUser);
            }

            return redirect()->route('home');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
