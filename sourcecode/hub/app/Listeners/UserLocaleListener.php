<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserSaved;
use App\Models\UserLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

final readonly class UserLocaleListener
{
    public function __construct(private Guard $guard, private Request $request)
    {
    }

    /**
     * Update the current locale when logging in.
     */
    public function handleUserLogin(Login $event): void
    {
        if ($event->user instanceof UserLogin) {
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
            $loggedIn instanceof UserLogin &&
            $event->user->is($loggedIn) &&
            $event->user->wasChanged('locale')
        ) {
            $this->request->session()->put('locale', $event->user->locale);
        }
    }

    /**
     * @return array<class-string, string>
     */
    public function subscribe(): array
    {
        return [
            Login::class => 'handleUserLogin',
            UserSaved::class => 'handleUserSaved',
        ];
    }
}
