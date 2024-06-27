<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\ContentVersion;

final readonly class ContentVersionDeleting
{
    public function __construct(public ContentVersion $version)
    {
    }
}