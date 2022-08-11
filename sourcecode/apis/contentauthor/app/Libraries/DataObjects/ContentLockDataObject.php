<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static ContentLockDataObject create($attributes = null)
 */
class ContentLockDataObject
{
    use CreateTrait;

    public $isLocked = false;
    public $editUrl;

}
