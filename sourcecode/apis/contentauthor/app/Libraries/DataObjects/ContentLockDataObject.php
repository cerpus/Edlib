<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class ContentLockDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static ContentLockDataObject create($attributes = null)
 */
class ContentLockDataObject
{
    use CreateTrait;

    public $isLocked = false;
    public $editUrl;

}
