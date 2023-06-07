<?php

namespace App\Http\Controllers;

use App\Configuration\Locales;
use App\Http\Requests\SavePreferencesRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        return view('user.my-account');
    }

    public function saveUsername(StoreUserRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        $user->update(['name' => $validatedData['name']]);

        return redirect()->route('user.myAccount')->with('alert', trans('messages.alert-profile-name-update'));
    }
}
