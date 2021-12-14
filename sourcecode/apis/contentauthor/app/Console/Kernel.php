<?php

namespace App\Console;

use App\Console\Commands\EnsureVersionExists;
use App\Console\Commands\Inspire;
use App\Console\Commands\CerpusSetup;
use App\Console\Commands\ListenToEdlibMessageBus;
use App\Console\Commands\PublishPresave;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\RemoveOldContentLocks;
use App\Console\Commands\VersionAllUnversionedContent;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CerpusSetup::class,
        EnsureVersionExists::class,
        Inspire::class,
        ListenToEdlibMessageBus::class,
        PublishPresave::class,
        RemoveOldContentLocks::class,
        VersionAllUnversionedContent::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
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

        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo($outputLocation);
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
