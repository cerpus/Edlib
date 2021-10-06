<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class H5PEditorConfigObject
 * @package App\Libraries\DataObjects
 *
 * @method static H5PEditorConfigObject create($attributes = null)
 */
class H5PEditorConfigObject extends EditorConfigObject
{
    use CreateTrait;

    public $libraryUpgradeList = [];

    public $autoTranslateTo;
    public $adapterName, $adapterList;
    public $hideNewVariant = false;
    public $showDisplayOptions = false;
    public $h5pLanguage;
    public $creatorName;

    public function toJson(): string
    {
        return parent::toJson();
    }
}
