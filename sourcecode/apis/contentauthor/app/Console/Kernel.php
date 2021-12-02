<?php

namespace App\Console;

use App\Bootstrap\LoadDefaultEnvVariables;
use App\Console\Commands\EnsureVersionExists;
use App\Console\Commands\Inspire;
use App\Console\Commands\CerpusSetup;
use App\Console\Commands\ListenToEdlibMessageBus;
use App\Console\Commands\PublishPresave;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\RemoveOldContentLocks;
use App\Console\Commands\VersionAllUnversionedContent;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
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

    public function __construct(Application $app, Dispatcher $events)
    {
        $this->bootstrappers = [
            LoadDefaultEnvVariables::class,
            ...$this->bootstrappers,
        ];

        parent::__construct($app, $events);
    }

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // PS! Always use '->onOneServer()'. In production CA is running on several servers...
        $schedule->command('cerpus:remove-content-locks')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();

        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onOneServer();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
