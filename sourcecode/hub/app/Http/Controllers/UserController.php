<?php

namespace App\Http\Controllers;

use App\Configuration\Locales;
use App\Http\Requests\SavePreferencesRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\ResetPasswordEmail;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


use function app;
use function to_route;
use function view;

class UserController extends Controller
{
    public function register(): View|RedirectResponse
    {
        if (Auth::check()) {
            return to_route('home');
        }

        return view('user.register');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User();
        $user->name = $request->validated('name');
        $user->email = $request->validated('email');
        $user->password = Hash::make($request->validated('password'));
        $user->save();

        Auth::login($user);

        return to_route('home');
    }

    public function preferences(Locales $locales): View
    {
        return view('user.preferences', [
            'locales' => $locales->getTranslatedMap(app()->getLocale()),
            'user' => $this->getUser(),
        ]);
    }

    public function savePreferences(SavePreferencesRequest $request): RedirectResponse
    {
        $user = $this->getUser();
        $user->fill($request->validated());
        $user->save();

        return to_route('user.preferences');
    }

    public function myAccount(): View
    {
        $user = Auth::user();
        return view('user.my-account', ['user' => $user]);
    }

    public function updateAccount(UpdateUserRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        /** @var User $user */
        $user = Auth::user();
        $user->name = $validatedData['name'];

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        return redirect()->route('user.my-account')->with('alert', trans('messages.alert-account-update'));
    }

    public function showForgotPasswordForm(): View
    {
        return view('user.forgot-password');
    }

    public function sendResetLink(Request $request): View|RedirectResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = base64_encode(random_bytes(48));

            $user->password_reset_token = $token;
            $user->save();

            $resetLink = route('reset-password', ['token' => $token, 'email' => $user->email]);

            Mail::to($user->email)->send(new ResetPasswordEmail($resetLink));
        }

        return redirect()->route('login')->with('alert', trans('messages.alert-password-reset'));
    }

    public function showResetPasswordForm(string $token, string $email): View|RedirectResponse
    {
        $user = User::where('password_reset_token', $token)->where('email', $email)->first();

        if ($user) {
            return view('user.reset-password', compact('token', 'email'));
        }

        return redirect()->back()->with('alert', trans('messages.alert-password-reset-invalid-token'));
    }

    public function resetPassword(Request $request, string $token): View|RedirectResponse
    {
        $user = User::where('password_reset_token', $token)->first();

        if ($user) {
            $request->validate([
                'password' => 'required|confirmed|min:8',
            ]);

            $user->password = bcrypt($request->password);
            $user->password_reset_token = null;
            $user->save();
            return redirect('/')->with('alert', trans('messages.alert-password-reset-success'));
        }

        return redirect()->back()->with('alert', trans('messages.alert-password-reset-invalid-token'));
    }
}
