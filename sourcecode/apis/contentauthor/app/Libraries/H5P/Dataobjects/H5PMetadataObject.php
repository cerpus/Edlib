<?php

namespace App\Libraries\H5P\Dataobjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static H5PMetadataObject create($attributes = null)
 */
class H5PMetadataObject
{
    use CreateTrait;

    public $title;
    public $authors;
    public $authorComments;
    public $changes;
    public $source;
    public $yearFrom;
    public $yearTo;
    public $license;
    public $licenseVersion;
    public $licenseExtras;
    public $defaultLanguage;

    public const H5PMetadataFieldsInOrder = [
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
