<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static H5PStateDataObject create($attributes = null)
 */
class H5PStateDataObject extends ContentStateDataObject
{
    use CreateTrait;

    public $library;
    public $libraryid;
    public $language_iso_639_3;
    public $isNewLanguageVariant = false;
    public $parameters = '{}';
    public $max_score;
    public $embed;
    public $download;
    public $frame;
    public $copyright;
}
