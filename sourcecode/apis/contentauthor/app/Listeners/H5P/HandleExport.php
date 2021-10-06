<?php


namespace App\Listeners\H5P;


use App\Events\H5PWasSaved;
use App\Jobs\ExportH5P;

class HandleExport
{

    public function handle(H5PWasSaved $event)
    {
        if( config('feature.export_h5p_on_save') ){
            ExportH5P::dispatch($event->h5p->refresh())->onQueue('h5p-export');
        }
    }
}
