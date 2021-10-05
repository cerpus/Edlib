<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class H5PStateDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static QuestionSetStateDataObject create($attributes = null)
 */
class QuestionSetStateDataObject extends ContentStateDataObject
{
    use CreateTrait;

    public $links, $questionSetJsonData, $contentTypes, $questionset;
    public $editmode, $presentation;

}
