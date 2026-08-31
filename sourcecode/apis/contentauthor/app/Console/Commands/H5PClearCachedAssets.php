<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\H5PLibrariesCachedAssets;
use App\Libraries\DataObjects\ContentStorageSettings;
use H5PFileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class H5PClearCachedAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:clear-cached-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete aggregated library assets, they will be regenerated on demand';

    /**
     * Execute the console command.
     */
    public function handle(H5PFileStorage $storage): int
    {
        $hashes = H5PLibrariesCachedAssets::distinct()->pluck('hash')->all();
        $storage->deleteCachedAssets($hashes);
        H5PLibrariesCachedAssets::query()->delete();

        // Files without a matching row will never be regenerated, so get rid of
        // those as well.
        $disk = Storage::disk();
        $orphans = 0;
        foreach ($disk->files(ContentStorageSettings::CACHEDASSETS_DIR) as $file) {
            $disk->delete($file);
            $orphans++;
        }

        $this->line(sprintf(
            'Deleted <fg=yellow>%d</> cached asset bundle(s), <fg=yellow>%d</> file(s) removed.',
            count($hashes),
            $orphans,
        ));

        return SymfonyCommand::SUCCESS;
    }
}
