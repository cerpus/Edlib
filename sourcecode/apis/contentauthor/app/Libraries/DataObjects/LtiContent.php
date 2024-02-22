<?php

declare(strict_types=1);

namespace App\Libraries\DataObjects;

final readonly class LtiContent
{
    /**
     * @param string $id
     *     This is only for backward compatibility, and should not be used for
     *     LTI interop in new systems.
     */
    public function __construct(
        public string $id,
        public string $url,
        public string $title,
        public string $machineName,
        public bool $hasScore,
        public string|null $titleHtml = null,
        public string|null $editUrl = null,
        public string|null $languageIso639_3 = null,
        public string|null $license = null,
        public string|null $iconUrl = null,
        public bool|null $published = null,
    ) {
    }
}
