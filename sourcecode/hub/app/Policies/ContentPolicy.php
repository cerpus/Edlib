<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function view(User $user, Content $content): bool
    {
        return $content->latestPublishedVersion()->exists();
    }

    public function copy(User $user, Content $content): bool
    {
        return true;
    }
}
