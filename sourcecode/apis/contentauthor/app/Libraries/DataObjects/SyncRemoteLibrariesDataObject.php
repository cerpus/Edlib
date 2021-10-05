<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class SyncRemoteLibrariesDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static SyncRemoteLibrariesDataObject create($attributes = null)
 */
class SyncRemoteLibrariesDataObject
{
    use CreateTrait;

    public $cacheKey, $seconds = 7200;

}