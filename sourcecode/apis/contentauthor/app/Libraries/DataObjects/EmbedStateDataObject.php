<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static EmbedStateDataObject create($attributes = null)
 */
class EmbedStateDataObject extends ContentStateDataObject
{
    use CreateTrait;

    public $link;

}
