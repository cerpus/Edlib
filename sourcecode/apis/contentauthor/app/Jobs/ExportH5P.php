<?php

namespace App\Jobs;

use App\H5PContent;
use App\Libraries\H5P\H5PExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportH5P implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $content, $adapter, $fileStorage, $localStorage;

    /**
     * ExportH5P constructor.
     * @param H5PContent $content
     */
    public function __construct(H5PContent $content)
    {
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $export = resolve(H5PExport::class, ['content' => $this->content]);
        return $export->generateExport(config('feature.export_h5p_with_local_files'));
    }
}
