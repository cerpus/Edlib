<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LtiPlatform;
use App\Models\User;

final readonly class LtiPlatformPolicy
{
    public function edit(User $user, LtiPlatform $platform): bool
    {
        return $user->admin;
    }

    public function delete(User $user, LtiPlatform $platform): bool
    {
        return $user->admin;
    }
}
