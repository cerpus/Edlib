<?php

namespace App\Libraries\H5P\Dataobjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static H5PCopyrightDataObject create($attributes = null)
 */
class H5PCopyrightDataObject
{
    use CreateTrait;

    public $title;
    public $source;
    public $yearFrom;
    public $yearTo;
    public $license;
    public $licenseVersion;
    public $licenseExtras;
    public $thumbnail;
    public $contentType;

    private $authors = [];

    public function addAuthor(H5PCopyrightAuthorDataObject $author)
    {
        $this->authors[] = $author;
    }

    public function getAuthors()
    {
        return $this->authors;
    }
}
