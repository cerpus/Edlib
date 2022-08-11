<?php


namespace App\Libraries\H5P\Dataobjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static H5PImportDataObject create($h5pId = null, $h5pType = null, $title = null, $maxScore = null)
 */
class H5PImportDataObject
{
    use CreateTrait;

    public $h5pId;
    public $h5pType;
    public $title;
    public $maxScore;
}