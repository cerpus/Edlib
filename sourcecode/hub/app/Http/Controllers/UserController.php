<?php

namespace App\Http\Controllers;

use App\Configuration\Locales;
use App\Http\Requests\SavePreferencesRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

use function app;
use function to_route;

class UserController extends Controller
{
    public function preferences(Locales $locales): View
    {
        return view('user.preferences', [
            'locales' => $locales->getTranslatedMap(app()->getLocale()),
            'user' => auth()->user(),
        ]);
    }

    public function savePreferences(SavePreferencesRequest $request): RedirectResponse
    {
        $user = auth()->user();
        assert($user instanceof User);

        $user->fill($request->validated());
        $user->save();

        return to_route('user.preferences');
    }
}
