<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class H5PStateDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static EmbedStateDataObject create($attributes = null)
 */
class EmbedStateDataObject extends ContentStateDataObject
{
    use CreateTrait;

    public $link;

}
