<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\UserLogin;

class UserSaved
{
    public function __construct(
        public readonly UserLogin $user,
    ) {
    }
}
