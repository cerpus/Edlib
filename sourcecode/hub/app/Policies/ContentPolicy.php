<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\User;
use Illuminate\Support\Facades\Session;

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

        if (Session::has('lti.oauth_consumer_key')) {
            $key = Session::get('lti.oauth_consumer_key');
            $platform = LtiPlatform::where('key', $key)->first();

            if (
                $platform?->authorizes_edit &&
                Session::has('intent-to-edit.' . $content->id)
            ) {
                return true;
            }
        }

        return $content->hasUser($user);
    }

    public function copy(
        User $user,
        Content $content,
        ContentVersion|null $version = null,
    ): bool {
        if ($content->hasUser($user)) {
            return true;
        }

        if (!$content->shared) {
            return false;
        }

        $version ??= $content->latestPublishedVersion;

        if ($version === null) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Content $content): bool
    {
        if ($content->trashed()) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        // TODO: check owner role
        return $content->hasUser($user);
    }

    public function use(User|null $user, Content $content, ContentVersion $version): bool
    {
        if (
            !request()->hasPreviousSession() ||
            !request()->session()->has('lti.content_item_return_url')
        ) {
            // not in LTI Deep Linking context
            return false;
        }

        if (!$version->content?->is($content)) {
            return false;
        }

        if (!$version->published) {
            return false;
        }

        return true;
    }
}
