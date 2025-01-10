<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ContentForceDeleting;
use App\Models\ContentVersion;

final readonly class ContentListener
{
    public function handleForceDeleting(ContentForceDeleting $event): void
    {
        $event->content->views()->delete();

        $event->content->versions()
            ->lazy()
            ->each(fn(ContentVersion $version) => $version->delete());

        $event->content->contexts()->detach();

        $event->content->tags()->detach();

        $event->content->users()->detach();
    }
}
