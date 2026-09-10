<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\H5PLibrariesCachedAssets;
use App\Libraries\DataObjects\ContentStorageSettings;
use H5PFileStorage;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class H5PClearCachedAssets extends Command implements Isolatable
{
    public const string CACHE_KEY_RUNNING = 'h5p_clearing_cached_assets';
    public const int CACHE_KEY_RUNNING_TTL = 1 * 60;

    /**
     * Clear the running cache key and command isolation mutex.
     */
    public static function clearLock(): void
    {
        Cache::forget(self::CACHE_KEY_RUNNING);

        try {
            $mutex = resolve(\Illuminate\Console\CommandMutex::class);
            $command = resolve(self::class);
            $mutex->forget($command);
        } catch (\Throwable) {
        }
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:clear-cached-assets {--unlock : Remove the running lock without deleting assets}';

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
        if ($this->option('unlock')) {
            self::clearLock();
            $this->info('Deletion lock has been removed.');

            return SymfonyCommand::SUCCESS;
        }

        Cache::put(self::CACHE_KEY_RUNNING, true, now()->addMinutes(self::CACHE_KEY_RUNNING_TTL));

        try {
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
        } finally {
            Cache::forget(self::CACHE_KEY_RUNNING);
        }

        return SymfonyCommand::SUCCESS;
    }
}
