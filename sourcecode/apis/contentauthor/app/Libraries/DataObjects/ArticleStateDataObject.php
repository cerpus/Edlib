<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class H5PStateDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static ArticleStateDataObject create($attributes = null)
 */
class ArticleStateDataObject extends ContentStateDataObject
{
    use CreateTrait;

    public $content;

}
