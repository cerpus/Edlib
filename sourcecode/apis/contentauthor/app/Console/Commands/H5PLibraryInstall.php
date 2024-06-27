<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Libraries\CliH5pAjax;
use App\H5PLibrariesHubCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            return Command::FAILURE;
        }

        $cacheUpdate = $this->call('h5p:library-hub-cache', ['--force' => $this->option('force-cache')]);
        if ($cacheUpdate === Command::SUCCESS) {
            $this->install();
        } else {
            return $cacheUpdate;
        }

        return Command::SUCCESS;
    }

    private function install(): int
    {
        $hasError = false;
        $libraries = $this->argument('library');

        foreach ($libraries as $library) {
            $cache = H5PLibrariesHubCache::where(DB::raw('lower(name)'), '=', Str::lower($library))->first();
            if (!$cache) {
                $hasError = true;
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
                $hasError = true;
                $this->error('Failed');
                if (isset($result['message'])) {
                    $this->error('      ' . $result['message']);
                }
            }
        }

        $this->newLine();

        return $hasError ? Command::FAILURE : Command::SUCCESS;
    }
}