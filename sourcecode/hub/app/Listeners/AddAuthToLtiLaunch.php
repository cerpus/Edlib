<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchLti;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;

use function preg_match;

final readonly class AddAuthToLtiLaunch
{
    public function __construct(private Guard $guard) {}

    public function handleLaunch(LaunchLti $event): void
    {
        $user = $this->guard->user();

        if (!$user instanceof User) {
            return;
        }

        $this->addUserDetails($event, $user);
        $this->addRoles($event, $user);
    }

    private function addUserDetails(LaunchLti $event, User $user): void
    {
        $tool = $event->getTool();
        $launch = $event->getLaunch()->withClaim('user_id', $user->id);

        if ($tool->send_name) {
            // Some systems need given & family names. We store the full name as
            // an opaque string, so all we can provide is a best guess. A
            // well-designed system should use the provided full name instead.
            if (preg_match('/^(.*) (.*?)$/', $user->name, $matches)) {
                $givenName = $matches[1];
                $familyName = $matches[2];
            } else {
                $givenName = $user->name;
                $familyName = '';
            }

            $launch = $launch
                ->withClaim('lis_person_name_full', $user->name)
                ->withClaim('lis_person_name_given', $givenName)
                ->withClaim('lis_person_name_family', $familyName);
        }

        if ($tool->send_email && $user->email_verified) {
            $launch = $launch
                ->withClaim('lis_person_contact_email_primary', $user->email);
        }

        $event->setLaunch($launch);
    }

    private function addRoles(LaunchLti $event, User $user): void
    {
        if (!$user->admin) {
            return;
        }

        $roles = $event->getLaunch()->getClaim('roles') ?? '';

        if ($roles !== '') {
            $roles .= ',Administrator';
        } else {
            $roles = 'Administrator';
        }

        $event->setLaunch($event->getLaunch()->withClaim('roles', $roles));
    }
}
