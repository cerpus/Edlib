<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static LockedDataObject create($attributes = null)
 */
class LockedDataObject
{
    use CreateTrait;

    public $editor;
    public $editUrl, $pollUrl;

}
