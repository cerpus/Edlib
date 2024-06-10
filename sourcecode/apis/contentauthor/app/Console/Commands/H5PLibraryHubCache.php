<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\H5POption;
use Carbon\Carbon;
use Illuminate\Console\Command;

class H5PLibraryHubCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:library-hub-cache
                                {--f|force   : Force update}
                                {--s|status  : Don\'t update the cache, just display current status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the local h5p.org library cache if older than 14 days. This will also register the installation with the h5p.org hub if not registered';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!config('h5p.isHubEnabled', false)) {
            $this->newLine();
            $this->warn("Use of h5p.org hub is not enabled. To enable set 'H5P_IS_HUB_ENABLED=true'");
            $this->newLine();

            return Command::FAILURE;
        }

        $lastUpdate = $this->lastUpdated();

        $this->newLine();
        $this->printIsRegistered();
        $this->printLastUpdated($lastUpdate);
        $this->output->write("Cache status: ");
        $shouldUpdate = $this->shouldUpdate($lastUpdate);

        if ($shouldUpdate !== false) {
            $this->info($shouldUpdate);

            if (!$this->option('status')) {
                return $this->updateCache();
            }
        } else {
            $this->info('Update not required');
        }

        $this->newLine();

        return Command::SUCCESS;
    }

    private function updateCache(): int
    {
        $ret = Command::FAILURE;
        $this->output->write('Updating: ');

        $core = app(\H5PCore::class);
        $data = $core->updateContentTypeCache();

        if ($data === false) {
            $this->error('Failed');
            foreach ($core->h5pF->getMessages('error') as $message) {
                $this->error($message);
            }
            $core->h5pF->clearErrorMessages();
        } else {
            foreach ($core->h5pF->getMessages('info') as $message) {
                $this->info($message);
            }
            $core->h5pF->clearInfoMessages();
            $ret = Command::SUCCESS;
        }

        $this->newLine();

        return $ret;
    }

    private function lastUpdated(): Carbon|null
    {
        $option = H5POption::where('option_name', 'content_type_cache_updated_at')->first();
        return $option ? Carbon::createFromTimestamp($option->option_value) : null;
    }

    private function printLastUpdated(Carbon|null $updated): void
    {
        if ($updated !== null) {
            $when = $updated->format('Y-m-d H:i:s e');
        } else {
            $when = 'Never';
        }

        $this->line("Cache updated: <fg=yellow>$when</>");
    }

    private function shouldUpdate(Carbon|null $lastUpdate): string|false
    {
        if (!$this->option('status') && $this->option('force')) {
            return 'Update forced';
        }

        if ($lastUpdate === null) {
            return 'First run';
        }

        if (Carbon::now()->diffInDays($lastUpdate) >= 14) {
            return 'Stale';
        }

        return false;
    }

    private function printIsRegistered(): void
    {
        $option = H5POption::where('option_name', 'site_uuid')->first();

        if ($option && $option->option_value !== '') {
            $this->info("Registered with h5p.org hub");
        } else {
            $this->warn("Not registered with h5p.org hub. Registration will be performed on next cache update");
        }
    }
}
