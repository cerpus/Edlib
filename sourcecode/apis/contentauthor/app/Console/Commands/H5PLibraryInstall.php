<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Libraries\CliH5pAjax;
use App\H5PLibrariesHubCache;
use App\H5POption;
use Carbon\Carbon;
use Illuminate\Console\Command;

class H5PLibraryInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:library-manage
                                {action   : install, update-cache}
                                {library? : For install, machinename of library to install or update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install or update H5P library from h5p.org Hub, update the local cache of available libraries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        if (!config('h5p.isHubEnabled', false)) {
            $this->newLine();
            $this->warn("Use of h5p.org hub is not enabled. To enable set 'H5P_IS_HUB_ENABLED=true' in your environment file");
            $this->newLine();

            return Command::FAILURE;
        }

        if ($action === 'update-cache') {
            return $this->updateCache();
        }

        if ($action === 'install') {
            if ($this->argument('library') === null) {
                $this->newLine();
                $this->error('Not enough arguments (missing "library").');
                $this->info('To see available libraries run "h5p:library-list available".');
                return Command::FAILURE;
            }

            return $this->install($this->argument('library'));
        }

        return Command::FAILURE;
    }

    private function updateCache(): int
    {
        $lastUpdate = H5POption::where('option_name', 'content_type_cache_updated_at')->first();
        if ($lastUpdate) {
            $when = Carbon::createFromTimestamp($lastUpdate->option_value)->format('Y-m-d H:i:s e');
        } else {
            $when = 'Never';
        }

        $this->line("Previous update was: <fg=yellow>$when</>");
        $this->line("Updating libraries list...");

        $core = app(\H5PCore::class);
        $data = $core->updateContentTypeCache();

        if ($data === false) {
            foreach($core->h5pF->getMessages('error') as $message) {
                $this->error($message);
                return Command::FAILURE;
            }
        }
        foreach($core->h5pF->getMessages('info') as $message) {
            $this->info($message);
        }

        return Command::SUCCESS;
    }

    private function install(string $library): int
    {
        $cache = H5PLibrariesHubCache::where('name', $library)->first();
        if (!$cache) {
            $this->error("Library '$library' was not found in the cache");
            return Command::FAILURE;
        }

        $result = json_decode(CliH5PAjax::installLibrary($library), true);

        if ($result['success']) {
            $this->info('Installation complete');
            if (isset($result['data']['details']) && count($result['data']['details']) > 0) {
                foreach($result['data']['details'] as $detail) {
                    $this->line($detail);
                }
            } else {
                $this->line('No libraries was installed or updated');
            }

            return Command::SUCCESS;
        } else {
            $this->info('Installation failed');
            if (isset($result['message'])) {
                $this->info($result['message']);
            }

            return Command::FAILURE;
        }
    }
}
