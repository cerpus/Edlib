<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\AccumulateViews;
use App\Jobs\PruneVersionlessContent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(PruneVersionlessContent::class)->daily();

        $schedule->command(AccumulateViews::class, ['1 week ago midnight'])
            ->withoutOverlapping()
            ->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
