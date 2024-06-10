<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\H5PLibrariesHubCache;
use App\H5PLibrary;
use App\H5POption;
use Carbon\Carbon;
use H5PCore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class H5PLibraryList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:library-list
                            {action       : installed, available, outdated}
                            {--r|runnable : For installed, only show runnable (i.e. content type) libraries}
                            {--a|all      : For available, also list installed libraries. For outdated, also list libraries where installed version is newer}
                            {--l|library= : Only list library with this machine name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List installed, available or outdated libraries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'installed':
                return $this->installed();

            case 'available':
                return $this->available();

            case 'outdated':
                return $this->outdated();
        }

        $this->error("Unknown action: '$action'. Valid actions are: installed, available, outdated.");

        return Command::INVALID;
    }

    private function installed(): int
    {
        $libraries = H5PLibrary::select(
            [
                'name',
                'major_version',
                'minor_version',
                'patch_version',
                'runnable',
            ]
        )
            ->when($this->option('library'), function ($query) {
                $query->where(DB::raw('lower(name)'), '=', Str::lower($this->option('library')));
            })
            ->when($this->option('runnable'), function ($query) {
                $query->where('runnable', 1);
            })
            ->orderBy('name')
            ->orderBy('major_version')
            ->orderBy('minor_version')
            ->orderBy('patch_version')
            ->get()
            ->map(function (H5PLibrary $library) {
                return [
                    $library->name,
                    sprintf('%d.%d.%d', $library->major_version, $library->minor_version, $library->patch_version),
                    $library->runnable ? 'Yes' : 'No',
                ];
            });

        $this->newLine();
        $count = $libraries->count();
        $this->printLibraryCount($count);

        if ($count > 0) {
            $this->table(
                ['Name', 'Version', 'Content type'],
                $libraries
            );
        }

        $this->newLine();

        return Command::SUCCESS;
    }

    private function available(): int
    {
        $this->newLine();
        $this->printCacheLastUpdated();
        $this->printH5PCoreVersion();

        $libraries = H5PLibrariesHubCache::select([
                'name',
                'major_version',
                'minor_version',
                'patch_version',
                'owner',
                'h5p_major_version',
                'h5p_minor_version',
            ])
            ->when($this->option('library'), function ($query) {
                $query->where(DB::raw('lower(name)'), '=', Str::lower($this->option('library')));
            })
            ->when(!$this->option('all'), function ($query) {
                $query->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('h5p_libraries as lib')
                        ->whereColumn('h5p_libraries_hub_cache.name', 'lib.name');
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function ($cache) {
                return [
                    $cache->name,
                    sprintf('%d.%d.%d', $cache->major_version, $cache->minor_version, $cache->patch_version),
                    sprintf('%d.%d', $cache->h5p_major_version, $cache->h5p_minor_version),
                    $cache->owner,
                ];
            })
            ->sortKeys();

        $count = $libraries->count();
        $this->printLibraryCount($count);

        if ($count > 0) {
            $this->table(
                ['Name', 'Version', 'H5P Core version', 'Creator'],
                $libraries
            );
        }

        $this->newLine();

        return Command::SUCCESS;
    }

    private function outdated(): int
    {
        $localIsNewer = $this->option('all');
        $upgrades = [];

        H5PLibrariesHubCache::select(['name', 'major_version', 'minor_version', 'patch_version'])
            ->when($this->option('library'), function ($query) {
                $query->where(DB::raw('lower(name)'), '=', Str::lower($this->option('library')));
            })
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('h5p_libraries')
                    ->whereColumn('h5p_libraries_hub_cache.name', 'h5p_libraries.name');
            })
            ->orderBy('name')
            ->get()
            ->each(function ($library) use (&$upgrades, $localIsNewer) {
                $installed = H5PLibrary::select(['major_version', 'minor_version', 'patch_version'])
                    ->where('name', $library->name)
                    ->orderBy('major_version', 'desc')
                    ->orderBy('minor_version', 'desc')
                    ->orderBy('patch_version', 'desc')
                    ->limit(1)
                    ->first();

                $mjL = $library->major_version > $installed->major_version;
                $mjE = $library->major_version === $installed->major_version;
                $mjM = $library->major_version < $installed->major_version;

                $miL = $library->minor_version > $installed->minor_version;
                $miE = $library->minor_version === $installed->minor_version;
                $miM = $library->minor_version < $installed->minor_version;

                $paL = $library->patch_version > $installed->patch_version;
                $paM = $library->patch_version < $installed->patch_version;

                $color = null;
                if ($mjL || ($mjE && $miL)) { // New major or minor version
                    $color = 'yellow';
                } elseif ($mjE && $miE && $paL) { // New patch version
                    $color = 'red';
                } elseif ($localIsNewer && ($mjM || ($mjE && $miM) || ($mjE && $miE && $paM))) { // Installed is newer
                    $color = 'green';
                }
                if ($color) {
                    $upgrades[] = [
                        $library->name,
                        sprintf('%d.%d.%d', $installed->major_version, $installed->minor_version, $installed->patch_version),
                        "<fg=$color>".sprintf('%d.%d.%d', $library->major_version, $library->minor_version, $library->patch_version).'</>',
                    ];
                }
            });

        $this->newLine();
        $this->line('Color legend:');
        $this->line(' - <fg=yellow>Major or minor</> release available, will be installed in addition to existing');
        $this->line(' - <fg=red>Patch</> release available, will replace existing library');
        $this->line(' - <fg=green>Installed</> version is newer (use -a option to list)');
        $this->newLine();

        $this->printCacheLastUpdated();
        $count = count($upgrades);
        $this->printLibraryCount($count);

        if ($count > 0) {
            $this->table(
                ['Name', 'Installed', 'On h5p.org Hub'],
                $upgrades
            );
        }

        $this->newLine();

        return Command::SUCCESS;
    }

    private function printCacheLastUpdated(): void
    {
        $lastUpdate = H5POption::where('option_name', 'content_type_cache_updated_at')->first();
        if ($lastUpdate) {
            $when = Carbon::createFromTimestamp($lastUpdate->option_value)->format('Y-m-d H:i:s e');
        } else {
            $when = 'Never';
        }

        $this->line('Library cache updated: <fg=yellow>' . $when . '</>');
    }

    private function printH5PCoreVersion(): void
    {
        $this->line('Installed H5P Core version: <fg=yellow>' . join('.', H5PCore::$coreApi) . '</>');
    }

    private function printLibraryCount(int $count): void
    {
        $this->line("Libraries found: <fg=yellow>$count</>");
    }
}
