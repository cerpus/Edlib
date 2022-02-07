<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Doku;
use Illuminate\Foundation\Auth\User;

class DokuPolicy
{
    public function access(User $user, Doku $doku): bool
    {
        // TODO: authentication does not exist yet
        return $user->id === $doku->creator_id;
    }
}
