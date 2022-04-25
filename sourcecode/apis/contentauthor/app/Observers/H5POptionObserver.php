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
        app(H5POptionsCache::class)->fresh();
    }
}
