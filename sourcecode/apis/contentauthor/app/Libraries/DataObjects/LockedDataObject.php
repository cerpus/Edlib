<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class LockedDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static LockedDataObject create($attributes = null)
 */
class LockedDataObject
{
    use CreateTrait;

    public $editor;
    public $editUrl, $pollUrl;

}
