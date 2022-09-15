<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static ArticleStateDataObject create($attributes = null)
 */
class ArticleStateDataObject extends ContentStateDataObject
{
    use CreateTrait;

    public $content;
}
