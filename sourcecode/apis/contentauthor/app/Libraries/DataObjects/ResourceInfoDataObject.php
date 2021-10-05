<?php

namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class ResourceInfoDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static ResourceInfoDataObject create($attributes = null)
 */
class ResourceInfoDataObject
{
    use CreateTrait;

    public $id, $createdAt, $type, $maxScore, $ownerName;
    public $customFields = [];
}
