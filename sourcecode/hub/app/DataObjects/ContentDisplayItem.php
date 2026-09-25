<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Support\SessionScope;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function app;
use function locale_get_display_language;
use function route;
use function strtoupper;

readonly class ContentDisplayItem
{
    /**
     * @param array<array-key, mixed>|string $users
     */
    public function __construct(
        public string $title,
        public CarbonImmutable|null $createdAt,
        public bool $isPublished,
        public int $viewsCount,
        public string $contentType,
        public string $languageIso639_3,
        public string|null $languageDisplayName,
        public array|string $users,
        public string|null $detailsUrl,
        public string|null $previewUrl,
        public string|null $useUrl,
        public string|null $editUrl,
        public string|null $shareUrl,
        public string|null $shareDialogUrl,
        public string|null $copyUrl,
        public string|null $deleteUrl,
        public string|null $lockedByUserName,
        public string|null $actionButtonsUrl = null,
        public string|null $id = null,
    ) {}

    public static function fromContent(
        Content $content,
        ContentVersion|null $version = null,
        bool $forUser = false,
        bool $showDrafts = false,
        int $viewsCount = 0,
        string|null $contentType = null,
        bool $includeActionButtonsUrl = false,
    ): self {
        $version ??= ($showDrafts ? $content->latestVersion : $content->latestPublishedVersion)
            ?? $content->latestVersion
            ?? throw new NotFoundHttpException();

        $canUse = Gate::allows('use', [$content, $version]);
        $canEdit = Gate::allows('edit', [$content, $version]);
        $canView = Gate::allows('view', $content);
        $canDelete = $forUser && Gate::allows('delete', $content);
        $canCopy = Gate::allows('copy', $content);

        $languageName = locale_get_display_language($version->language_iso_639_3, app()->getLocale());
        $languageName = (!empty($languageName) && $languageName !== $version->language_iso_639_3) ? $languageName : null;

        return new self(
            title: $version->title,
            createdAt: $version->created_at?->toImmutable(),
            isPublished: $version->published,
            viewsCount: $viewsCount,
            contentType: $contentType ?? $version->displayed_content_type,
            languageIso639_3: strtoupper($version->language_iso_639_3),
            languageDisplayName: $languageName,
            users: $content->users->map(fn($user) => $user->name)->join(', '),
            detailsUrl: $showDrafts ? route('content.version-details', [$content, $version]) : route('content.details', [$content]),
            previewUrl: route('content.preview', [$content, $version]),
            useUrl: $canUse ? route('content.use', [$content, $version]) : null,
            editUrl: $canEdit ? route('content.edit', [$content, $version]) : null,
            shareUrl: $canView ? route('content.share', [$content, SessionScope::TOKEN_PARAM => null]) : null,
            shareDialogUrl: $canView ? route('content.share-dialog', [$content]) : null,
            copyUrl: $canCopy ? route('content.copy', [$content]) : null,
            deleteUrl: $canDelete ? route('content.delete', [$content]) : null,
            lockedByUserName: $content->getActiveLock()?->user?->name,
            actionButtonsUrl: ($includeActionButtonsUrl && $canEdit) ? route('content.action-buttons', [
                $content,
                'version' => $version->id,
                'forUser' => $forUser ? 1 : 0,
                'showDrafts' => $showDrafts ? 1 : 0,
            ]) : null,
            id: $content->id,
        );
    }
}
