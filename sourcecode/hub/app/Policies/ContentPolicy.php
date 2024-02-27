<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;

use function request;

class ContentPolicy
{
    public function view(
        User|null $user,
        Content $content,
        ContentVersion|null $version = null
    ): bool {
        if ($user?->admin) {
            return true;
        }

        $version ??= $content->latestPublishedVersion;

        if ($version?->published) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $content->hasUser($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function edit(
        User $user,
        Content $content,
        ContentVersion|null $version = null,
    ): bool {
        if ($version && !$version->content()->is($content)) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        return $content->hasUser($user);
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
