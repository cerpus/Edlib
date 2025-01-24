<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Context;

final readonly class ContextDeleting
{
    public function __construct(public Context $context) {}
}
