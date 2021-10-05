<?php

namespace App\Observers;

use App\H5POption;
use App\Libraries\H5P\Helper\H5POptionsCache;
use Illuminate\Support\Facades\Log;

class H5POptionObserver
{
    // Update the Options Cache when saving option
    public function saved(H5POption $option)
    {
        try {
            app(H5POptionsCache::class)->fresh();
        } catch (\Throwable $t) {
            Log::error(__METHOD__.": Failed to update H5POptionsCache. ({$t->getCode()}) {$t->getMessage()}");
        }
    }
}
