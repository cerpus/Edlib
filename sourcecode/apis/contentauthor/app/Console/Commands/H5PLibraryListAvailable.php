<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\H5PLibrariesHubCache;
use App\H5POption;
use Carbon\Carbon;
use H5PCore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class H5PLibraryListAvailable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:library-list:available
                            {--a|all               : Also list installed libraries}
                            {--l|library=          : Only list library with this machine name}
                            {--ignore-core-version : Also list libraries that require newer version of H5P Core than is installed, these will fail to install}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List libraries that are available from h5p.org Hub';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->printCacheLastUpdated();
        $this->line('Installed H5P Core version: <fg=yellow>' . join('.', H5PCore::$coreApi) . '</>');

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
            ->map(function ($cache) {
                $cMjL = $cache->h5p_major_version > H5PCore::$coreApi['majorVersion'];
                $cMjE = $cache->h5p_major_version === H5PCore::$coreApi['majorVersion'];
                $cMiL = $cache->h5p_minor_version > H5PCore::$coreApi['minorVersion'];

                $coreVersion = sprintf('%d.%d', $cache->h5p_major_version, $cache->h5p_minor_version);
                if ($cMjL || ($cMjE && $cMiL)) { // Requires newer H5P Core version
                    $coreVersion = "<fg=red>" . $coreVersion . '</>';
                }
                return [
                    $cache->name,
                    sprintf('%d.%d.%d', $cache->major_version, $cache->minor_version, $cache->patch_version),
                    $coreVersion,
                    $cache->owner,
                ];
            })
            ->sortKeys();

        $count = $libraries->count();
        $this->line("Libraries found: <fg=yellow>$count</>");

        if ($count > 0) {
            $this->table(
                ['Name', 'Version', 'H5P Core version', 'Creator'],
                $libraries,
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
