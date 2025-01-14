<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ContextDeleting;

final readonly class ContextListener
{
    public function handleDeleting(ContextDeleting $event): void
    {
        $event->context->contents()->detach();

        $event->context->platforms()->detach();
    }
}
