<?php

namespace App\Jobs;

use App\Libraries\DataObjects\SyncRemoteLibrariesDataObject;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncRemoteLibraries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $config;

    public function __construct(SyncRemoteLibrariesDataObject $remoteLibrariesDataObject)
    {
        $this->config = $remoteLibrariesDataObject;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        \Artisan::call('cerpus:copy-remote-libraries');
        if( !empty($this->config->cacheKey)){
            Cache::forget($this->config->cacheKey);
        }
    }
}
