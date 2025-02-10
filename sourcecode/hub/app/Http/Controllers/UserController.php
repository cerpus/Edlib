<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Configuration\Locales;
use App\Configuration\Themes;
use App\Http\Requests\RequestPasswordReset;
use App\Http\Requests\ResetPassword;
use App\Http\Requests\SavePreferencesRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\ResetPasswordEmail;
use App\Mail\VerifyEmailAddress;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use function app;
use function assert;
use function redirect;
use function route;
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
        $user->email_verified = false;
        $user->password = Hash::make($request->validated('password'));
        $user->save();

        Auth::login($user);

        return to_route('home');
    }

    public function preferences(Locales $locales, Themes $themes, Request $request): View
    {
        return view('user.preferences', [
            'canChangeLocale' => !$request->session()->has('lti.launch_presentation_locale'),
            'locales' => $locales->getTranslatedMap(app()->getLocale()),
            'themes' => $themes->getTranslatedMap(),
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
        return view('user.my-account', [
            'user' => $this->getUser(),
        ]);
    }

    public function updateAccount(UpdateUserRequest $request): RedirectResponse
    {
        $user = $this->getUser();
        $user->name = $request->validated('name');
        $user->email = $request->validated('email');

        if (!empty($request->validated('password'))) {
            $user->password = Hash::make($request->validated('password'));
        }

        $user->save();

        Auth::login($user);

        return redirect()
            ->route('user.my-account')
            ->with('alert', trans('messages.alert-account-update'));
    }

    public function showForgotPasswordForm(): View
    {
        return view('user.forgot-password');
    }

    public function sendResetLink(RequestPasswordReset $request): RedirectResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if ($user) {
            $token = hash('xxh128', random_bytes(48));

            $user->password_reset_token = $token;
            $user->save();

            $resetLink = route('reset-password', [$token]);

            Mail::to($user->email)->send(new ResetPasswordEmail($resetLink));
        }

        return redirect()
            ->route('login')
            ->with('alert', trans('messages.alert-password-reset'));
    }

    public function showResetPasswordForm(User $user): View
    {
        assert($user->password_reset_token !== null);

        return view('user.reset-password', [
            'user' => $user,
        ]);
    }

    public function resetPassword(ResetPassword $request, User $user): RedirectResponse
    {
        assert($user->password_reset_token !== null);

        $user->password = Hash::make($request->validated('password'));
        $user->password_reset_token = null;
        $user->save();

        return redirect()
            ->route('login')
            ->with('alert', trans('messages.alert-password-reset-success'));
    }

    public function disconnectSocialAccounts(Request $request): RedirectResponse
    {
        $user = $this->getUser();

        if ($request->has('disconnect-google')) {
            $user->google_id = null;
        }

        if ($request->has('disconnect-facebook')) {
            $user->facebook_id = null;
        }

        $user->save();

        return redirect()
            ->route('user.my-account')
            ->with('alert', trans('messages.alert-account-update'));
    }

    public function sendVerificationEmail(Mailer $mailer): RedirectResponse
    {
        $user = $this->getUser();

        if ($user->email_verified) {
            return redirect()->back();
        }

        $mailer->send(new VerifyEmailAddress($user));

        return redirect()->back()->with('alert', trans('messages.verification-email-sent'));
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        $user = $this->getUser();

        if (!$user->checkVerificationDetails(
            $request->query->getString('hash'),
            $request->query->getInt('time'),
        )) {
            abort(404);
        }

        $user->email_verified = true;
        $user->save();

        return redirect()->route('user.my-account')
            ->with('alert', trans('messages.your-account-has-been-verified'));
    }
}
