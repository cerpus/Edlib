<?php

namespace App\Libraries\DataObjects;


use App\Traits\CreateTrait;


/**
 * Class ResourceMetadataDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static ResourceMetadataDataObject create($attributes = null)
 */
class ResourceMetadataDataObject
{
    use CreateTrait;

    public $license, $share, $reason, $owner, $theSession, $tags;

}