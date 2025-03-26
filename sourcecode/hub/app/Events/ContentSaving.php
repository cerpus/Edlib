<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Content;

final readonly class ContentSaving
{
    public function __construct(public Content $content) {}
}
