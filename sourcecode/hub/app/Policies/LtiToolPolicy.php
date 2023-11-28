<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LtiTool;
use App\Models\User;

final readonly class LtiToolPolicy
{
    public function remove(User $user, LtiTool $tool): bool
    {
        return $user->admin && $tool->contentVersions()->count() === 0;
    }
}
