<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use App\Models\User;

final readonly class LtiToolPolicy
{
    public function launchCreator(User $user, LtiTool $tool, LtiToolExtra|null $extra): bool
    {
        if ($extra?->admin && !$user->admin) {
            return false;
        }

        return true;
    }

    public function addExtra(User $user, LtiTool $tool): bool
    {
        return $user->admin;
    }

    public function remove(User $user, LtiTool $tool): bool
    {
        return $user->admin && $tool->contentVersions()->count() === 0;
    }

    public function removeExtra(User $user, LtiTool $tool, LtiToolExtra $extra): bool
    {
        return $user->admin;
    }
}
