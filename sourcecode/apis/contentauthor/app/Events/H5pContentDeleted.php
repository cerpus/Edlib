<?php

declare(strict_types=1);

namespace App\Events;

use App\H5PContent;

final readonly class H5pContentDeleted
{
    public function __construct(public H5PContent $content) {}
}
