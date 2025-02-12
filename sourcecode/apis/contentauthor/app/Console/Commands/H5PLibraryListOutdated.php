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
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class H5PLibraryListOutdated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:library-list:outdated
                            {--a|all               : Also list libraries where installed version is newer}
                            {--l|library=          : Only list library with this machine name}
                            {--ignore-core-version : Also list libraries that require newer version of H5P Core than is installed, these will fail to install}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List libraries that can be updated from h5p.org Hub';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $localIsNewer = $this->option('all');
        $upgrades = [];

        H5PLibrariesHubCache::select(['name', 'major_version', 'minor_version', 'patch_version', 'h5p_major_version', 'h5p_minor_version'])
            ->when($this->option('library'), function ($query) {
                $query->where(DB::raw('lower(name)'), '=', Str::lower($this->option('library')));
            })
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('h5p_libraries')
                    ->whereColumn('h5p_libraries_hub_cache.name', 'h5p_libraries.name');
            })
            ->when(!$this->option('ignore-core-version'), function ($query) {
                $query->where(function ($query) {
                    $query->where('h5p_libraries_hub_cache.h5p_major_version', '<', H5PCore::$coreApi['majorVersion'])
                        ->orWhere(function ($query) {
                            $query->where('h5p_libraries_hub_cache.h5p_major_version', '=', H5PCore::$coreApi['majorVersion'])
                                ->where('h5p_libraries_hub_cache.h5p_minor_version', '<=', H5PCore::$coreApi['minorVersion']);
                        });
                });
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

                $cMjL = $library->h5p_major_version > H5PCore::$coreApi['majorVersion'];
                $cMjE = $library->h5p_major_version === H5PCore::$coreApi['majorVersion'];

                $cMiL = $library->h5p_minor_version > H5PCore::$coreApi['minorVersion'];

                $color = null;

                if ($cMjL || ($cMjE && $cMiL)) { // Requires newer H5P Core version
                    $color = 'red';
                } elseif ($mjL || ($mjE && $miL)) { // New major or minor version
                    $color = 'green';
                } elseif ($mjE && $miE && $paL) { // New patch version
                    $color = 'yellow';
                } elseif ($localIsNewer && ($mjM || ($mjE && $miM) || ($mjE && $miE && $paM))) { // Installed is newer
                    $color = 'magenta';
                }
                if ($color) {
                    $upgrades[] = [
                        $library->name,
                        sprintf('%d.%d.%d', $installed->major_version, $installed->minor_version, $installed->patch_version),
                        "<fg=$color>" . sprintf('%d.%d.%d', $library->major_version, $library->minor_version, $library->patch_version) . '</>',
                        sprintf('%d.%d', $library->h5p_major_version, $library->h5p_minor_version),
                    ];
                }
            });

        $this->newLine();
        $this->line('Color legend:');
        $this->line(' - <fg=green>Major or minor</> release available, will be installed in addition to existing');
        $this->line(' - <fg=yellow>Patch</> release available, will replace existing library');
        $this->line(' - <fg=magenta>Installed</> version is newer (use -a option to list)');
        $this->line(' - <fg=red>Uninstallable</> Requires newer version of H5P Core than installed (use --ignore-core-version option to list)');
        $this->newLine();

        $this->line('Installed H5P Core version: <fg=yellow>' . join('.', H5PCore::$coreApi) . '</>');
        $this->printCacheLastUpdated();
        $count = count($upgrades);
        $this->line("Libraries found: <fg=yellow>$count</>");

        if ($count > 0) {
            $this->table(
                ['Name', 'Installed', 'On h5p.org Hub', 'H5P Core version required'],
                $upgrades,
            );
        }

        $this->newLine();

        return SymfonyCommand::SUCCESS;
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
}
