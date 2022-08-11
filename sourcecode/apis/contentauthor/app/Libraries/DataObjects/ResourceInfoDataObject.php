<?php

namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static ResourceInfoDataObject create($attributes = null)
 */
class ResourceInfoDataObject
{
    use CreateTrait;

    public $id, $createdAt, $type, $maxScore, $ownerName;
    public $customFields = [];
}
