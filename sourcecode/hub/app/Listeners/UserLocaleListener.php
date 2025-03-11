<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchLti;
use App\Events\UserSaved;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use function app;

final readonly class UserLocaleListener
{
    public function __construct(private Guard $guard, private Request $request) {}

    /**
     * Update the current locale when logging in.
     */
    public function handleUserLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->request->session()->put('locale', $event->user->locale);
        }
    }

    /**
     * Update the current locale when saving preferences.
     */
    public function handleUserSaved(UserSaved $event): void
    {
        $loggedIn = $this->guard->user();

        if (
            $loggedIn instanceof User &&
            $event->user->is($loggedIn) &&
            $event->user->wasChanged('locale')
        ) {
            $this->request->session()->put('locale', $event->user->locale);
        }
    }

    /**
     * Include the locale in LTI launches
     */
    public function handleLtiLaunch(LaunchLti $event): void
    {
        $locale = app()->getLocale();

        $event->setLaunch(
            $event->getLaunch()->withClaim('launch_presentation_locale', $locale),
        );
    }
}
