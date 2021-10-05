<?php

namespace Tests\Traits;


use App\Libraries\H5P\h5p;
use App\Libraries\H5P\H5Plugin;

trait ResetH5PStatics
{
    public function setupResetH5PStatics()
    {
        h5p::setUp();
        H5Plugin::setUp();
    }
}