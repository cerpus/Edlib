<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ContentRole;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\User;
use Illuminate\Http\Request;
use LogicException;

readonly class ContentPolicy
{
    public function __construct(private Request $request) {}

    public function view(
        User|null $user,
        Content $content,
        ContentVersion|null $version = null,
    ): bool {
        $this->ensureVersionBelongsToContent($content, $version);

        if ($user?->admin) {
            return true;
        }

        $version ??= $content->latestPublishedVersion;

        if ($version?->published) {
            return true;
        }

        if ($this->hasContentRole(ContentRole::Reader, $content, $user)) {
            return true;
        }

        return false;
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
        $this->ensureVersionBelongsToContent($content, $version);

        if ($user->admin) {
            return true;
        }

        $platform = $this->getLtiPlatform();

        if (
            $platform?->authorizes_edit &&
            $this->request->session()->has('intent-to-edit.' . $content->id)
        ) {
            return true;
        }

        if ($this->hasContentRole(ContentRole::Editor, $content, $user, $platform)) {
            return true;
        }

        return false;
    }

    public function copy(
        User $user,
        Content $content,
        ContentVersion|null $version = null,
    ): bool {
        $this->ensureVersionBelongsToContent($content, $version);

        if ($user->admin) {
            return true;
        }

        if ($this->hasContentRole(ContentRole::Reader, $content, $user)) {
            return true;
        }

        if (!$content->shared) {
            return false;
        }

        $version ??= $content->latestPublishedVersion;

        if (!$version?->published) {
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

        if ($this->hasContentRole(ContentRole::Owner, $content, $user)) {
            return true;
        }

        return false;
    }

    public function use(User|null $user, Content $content, ContentVersion $version): bool
    {
        $this->ensureVersionBelongsToContent($content, $version);

        if (
            !$this->request->hasPreviousSession() ||
            !$this->request->session()->has('lti.content_item_return_url')
        ) {
            // not in LTI Deep Linking context
            return false;
        }

        if (!$version->published) {
            return false;
        }

        return true;
    }

    public function manageRoles(User $user, Content $content): bool
    {
        if ($user->admin) {
            return true;
        }

        return $this->hasContentRole(ContentRole::Owner, $content, $user);
    }

    private function ensureVersionBelongsToContent(Content $content, ContentVersion|null $version): void
    {
        if ($version && (
            $version->content_id !== $content->id ||
                $version->exists === false ||
                $content->exists === false ||
                $version->getConnectionName() !== $content->getConnectionName()
        )
        ) {
            throw new LogicException('Version does not belong to content');
        }
    }

    private function hasContentRole(
        ContentRole $role,
        Content $content,
        User|null $user = null,
        LtiPlatform|null $platform = null,
    ): bool {
        if ($user && $content->hasUserWithMinimumRole($user, $role)) {
            return true;
        }

        $platform ??= $this->getLtiPlatform();

        if ($platform) {
            foreach ($content->contexts as $context) {
                if ($platform->hasContextWithMinimumRole($context, $role)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getLtiPlatform(): LtiPlatform|null
    {
        $key = $this->request->session()->get('lti.oauth_consumer_key');

        if (!$key) {
            return null;
        }

        return LtiPlatform::where('key', $key)->first();
    }
}
