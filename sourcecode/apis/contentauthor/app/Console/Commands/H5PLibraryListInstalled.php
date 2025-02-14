<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\H5PLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class H5PLibraryListInstalled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:library-list:installed
                            {--r|runnable          : For installed, only show runnable (i.e. content type) libraries}
                            {--l|library=          : Only list library with this machine name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List installed libraries';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $libraries = H5PLibrary::select(
            [
                'name',
                'major_version',
                'minor_version',
                'patch_version',
                'runnable',
            ],
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
        $this->line("Libraries found: <fg=yellow>$count</>");

        if ($count > 0) {
            $this->table(
                ['Name', 'Version', 'Content type'],
                $libraries,
            );
        }

        $this->newLine();

        return SymfonyCommand::SUCCESS;
    }
}
