<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;

class UserSaved
{
    public function __construct(
        public readonly User $user,
    ) {}
}
