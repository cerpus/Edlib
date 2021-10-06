<?php


namespace App\Libraries\H5P\Dataobjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class H5PAlterParametersSettingsDataObject
 * @package App\Libraries\H5P\Dataobjects
 *
 * @method static H5PAlterParametersSettingsDataObject create($attributes = null)
 */
class H5PAlterParametersSettingsDataObject
{
    use CreateTrait;

    public $useImageWidth = true;
}
