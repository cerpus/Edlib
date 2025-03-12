<?php

declare(strict_types=1);

namespace App\Listeners\H5P;

use App\Events\H5pContentDeleted;
use App\Events\H5pContentUpdated;
use Illuminate\Cache\Repository;

final readonly class InfoCacheListener
{
    public function __construct(private Repository $cache) {}

    public function handleUpdated(H5pContentUpdated $event): void
    {
        $this->cache->forget($event->content->getCopyrightCacheKey());
        $this->cache->forget($event->content->getInfoCacheKey());
    }

    public function handleDeleted(H5pContentDeleted $event): void
    {
        $this->cache->forget($event->content->getCopyrightCacheKey());
        $this->cache->forget($event->content->getInfoCacheKey());
    }
}
