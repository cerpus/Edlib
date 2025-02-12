<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Libraries\CliH5pAjax;
use App\H5PLibrariesHubCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class H5PLibraryInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:library-install
                                {library*      : Name of library to install or update}
                                {--force-cache : Force update of library cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install or update H5P libraries from h5p.org hub, cache is updated if required';
    protected $help = 'Install or update select libraries from h5p.org hub. If the local h5p.org hub cache is empty or stale, it will be updated before install/update, this will also register the installation with h5p.org hub if not registered.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!config('h5p.isHubEnabled', false)) {
            $this->newLine();
            $this->warn("Use of h5p.org hub is not enabled. To enable set 'H5P_IS_HUB_ENABLED=true'");
            $this->newLine();

            return SymfonyCommand::FAILURE;
        }

        $cacheUpdate = $this->call('h5p:library-hub-cache', ['--force' => $this->option('force-cache')]);

        if ($cacheUpdate !== SymfonyCommand::SUCCESS) {
            return $cacheUpdate;
        }

        return $this->install() ? SymfonyCommand::SUCCESS : SymfonyCommand::FAILURE;
    }

    /**
     * @return bool True if success, otherwise false
     */
    private function install(): bool
    {
        $success = true;
        $libraries = $this->argument('library');

        foreach ($libraries as $library) {
            $cache = H5PLibrariesHubCache::where(DB::raw('lower(name)'), '=', Str::lower($library))->first();
            if (!$cache) {
                $success = false;
                $this->error("   - $library: Not found in cache, skipping");
                continue;
            }

            $this->output->write("   - $cache->name: ");
            $result = json_decode(CliH5pAjax::installLibrary($library), true);

            if ($result['success']) {
                if (isset($result['data']['details']) && count($result['data']['details']) > 0) {
                    foreach ($result['data']['details'] as $detail) {
                        $this->info($detail);
                    }
                } else {
                    $this->info('No change, already up to date');
                }
            } else {
                $success = false;
                $this->error('Failed');
                if (isset($result['message'])) {
                    $this->error("\t" . $result['message']);
                }
                if (isset($result['details'])) {
                    foreach ($result['details'] as $detail) {
                        $this->error("\t" . $detail);
                    }
                }
            }
        }

        $this->newLine();

        return $success;
    }
}
