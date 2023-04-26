<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

final class RebuildContentIndex implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(Kernel $artisan): void
    {
        if ($artisan->call('scout:delete-index', ['name' => Content::class]) !== 0) {
            $this->fail('Calling scout:delete-index failed');
            return;
        }

        if ($artisan->call('scout:sync-index-settings') !== 0) {
            $this->fail('Calling scout:sync-index-settings failed');
            return;
        }

        if ($artisan->call('scout:import', ['model' => Content::class]) !== 0) {
            $this->fail('Calling scout:import failed');
        }
    }
}
