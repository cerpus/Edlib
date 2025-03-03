<?php

namespace App\Libraries\H5P\Interfaces;

use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;

interface H5PImageInterface extends LoadsCustomAssets
{
    public function alterImageProperties($imageProperties, H5PAlterParametersSettingsDataObject $settings): object;
}
