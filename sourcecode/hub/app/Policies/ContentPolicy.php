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
    public function __construct(private Request $request)
    {
    }

    public function view(
        User|null $user,
        Content $content,
        ContentVersion|null $version = null
    ): bool {
        $this->ensureVersionBelongsToContent($content, $version);

        if ($user?->admin) {
            return true;
        }

        $version ??= $content->latestPublishedVersion;

        if ($version?->published) {
            return true;
        }

        $platform = $this->getLtiPlatform();
        if ($platform) {
            foreach ($content->contexts ?? [] as $context) {
                if ($platform->hasContextWithMinimumRole($context, ContentRole::Reader)) {
                    return true;
                }
            }
        }

        if (!$user) {
            return false;
        }

        return $content->hasUserWithMinimumRole($user, ContentRole::Reader);
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

        if ($platform) {
            if (
                $platform->authorizes_edit &&
                $this->request->session()->has('intent-to-edit.' . $content->id)
            ) {
                return true;
            }

            foreach ($content->contexts ?? [] as $context) {
                if ($platform->hasContextWithMinimumRole($context, ContentRole::Editor)) {
                    return true;
                }
            }
        }

        return $content->hasUserWithMinimumRole($user, ContentRole::Editor);
    }

    public function copy(
        User $user,
        Content $content,
        ContentVersion|null $version = null,
    ): bool {
        $this->ensureVersionBelongsToContent($content, $version);

        $platform = $this->getLtiPlatform();
        if ($platform) {
            foreach ($content->contexts ?? [] as $context) {
                if ($platform->hasContextWithMinimumRole($context, ContentRole::Reader)) {
                    return true;
                }
            }
        }

        if ($content->hasUserWithMinimumRole($user, ContentRole::Reader)) {
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

        $platform = $this->getLtiPlatform();
        if ($platform) {
            foreach ($content->contexts ?? [] as $context) {
                if ($platform->hasContextWithMinimumRole($context, ContentRole::Owner)) {
                    return true;
                }
            }
        }

        return $content->hasUserWithMinimumRole($user, ContentRole::Owner);
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

        if (!$version->content?->is($content)) {
            return false;
        }

        if (!$version->published) {
            return false;
        }

        return true;
    }

    public function manageRoles(User $user, Content $content): bool
    {
        if ($content->hasUserWithMinimumRole($user, ContentRole::Owner)) {
            return true;
        }

        return false;
    }

    private function ensureVersionBelongsToContent(Content $content, ContentVersion|null $version): void
    {
        if ($version && !$version->content?->is($content)) {
            throw new LogicException('Version does not belong to content');
        }
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
