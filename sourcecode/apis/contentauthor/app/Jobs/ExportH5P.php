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
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly H5PContent $content) {}

    public function handle(H5PExport $export): void
    {
        $export->generateExport($this->content);
    }
}
