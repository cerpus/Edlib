<?php

namespace App\Libraries\H5P\Dataobjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class H5PMetadataObject
 * @package App\Libraries\H5P\Dataobjects
 *
 * @method static H5PMetadataObject create($attributes = null)
 */
class H5PMetadataObject
{
    use CreateTrait;

    public $title;
    public $authors, $authorComments, $changes;
    public $source, $yearFrom, $yearTo;
    public $license, $licenseVersion, $licenseExtras;
    public $defaultLanguage;

    const H5PMetadataFieldsInOrder = [
        'title',
        'authors',
        'changes',
        'source',
        'license',
        'licenseVersion',
        'licenseExtras',
        'authorComments',
        'yearFrom',
        'yearTo',
        'defaultLanguage',
    ];
}