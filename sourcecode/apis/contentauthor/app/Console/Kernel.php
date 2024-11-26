<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $outputLocation = '/proc/1/fd/1';
        // PS! Always use '->onOneServer()'. In production CA is running on several servers...
        $schedule->command('cerpus:remove-content-locks')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo($outputLocation);
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
