<?php

namespace App\Libraries\H5P\Dataobjects;

class H5PAlterParametersSettingsDataObject
{
    public function __construct(
        public bool $useImageWidth = true,
    ) {}
}
