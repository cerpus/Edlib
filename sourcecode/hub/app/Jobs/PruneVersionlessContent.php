<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Prune content without attached versions.
 *
 * Generous leeway is given to avoid race conditions with the REST API.
 */
final class PruneVersionlessContent implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(): void
    {
        Content::withoutGlobalScope('atLeastOneVersion')
            ->whereDoesntHave('versions')
            ->whereDate('updated_at', '<', now()->subHours(24))
            ->lazy()
            ->each(fn(Content $content) => $content->forceDelete());
    }
}
