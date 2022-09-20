<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static ResourceInfoDataObject create($attributes = null)
 */
class ResourceInfoDataObject
{
    use CreateTrait;

    public $id;
    public $createdAt;
    public $type;
    public $maxScore;
    public $ownerName;
    public $customFields = [];
}
