<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

use function request;

class ContentPolicy
{
    public function view(User|null $user, Content $content): bool
    {
        if ($user?->admin) {
            return true;
        }

        return $content->latestPublishedVersion()->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function edit(User $user, Content $content): bool
    {
        if ($user->admin) {
            return true;
        }

        return $content->users()->where('id', $user->id)->exists();
    }

    public function copy(User $user, Content $content): bool
    {
        return true;
    }

    public function use(User|null $user, Content $content): bool
    {
        return $content->latestPublishedVersion()->exists() &&
            request()->hasPreviousSession() &&
            request()->session()->has('lti.content_item_return_url');
    }
}
