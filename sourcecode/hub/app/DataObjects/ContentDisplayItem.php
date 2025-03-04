<?php

declare(strict_types=1);

namespace App\DataObjects;

use Carbon\CarbonImmutable;

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
    ) {}
}
