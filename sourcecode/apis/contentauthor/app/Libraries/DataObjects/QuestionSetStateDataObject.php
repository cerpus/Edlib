<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static QuestionSetStateDataObject create($attributes = null)
 */
class QuestionSetStateDataObject extends ContentStateDataObject
{
    use CreateTrait;

    public $links;
    public $questionSetJsonData;
    public $contentTypes;
    public $questionset;
    public $editmode;
    public $presentation;
}
