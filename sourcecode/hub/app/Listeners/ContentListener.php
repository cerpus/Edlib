<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ContentDeleting;

final readonly class ContentListener
{
    public function handleDeleting(ContentDeleting $event): void
    {
        $event->content->versions()->delete();

        $event->content->users()->detach();
    }
}
