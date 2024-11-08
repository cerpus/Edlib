<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static H5PEditorConfigObject create($attributes = null)
 */
class H5PEditorConfigObject extends EditorConfigObject
{
    use CreateTrait;

    public $libraryUpgradeList = [];

    public $adapterName;
    public $adapterList;
    public $showDisplayOptions = false;
    public $h5pLanguage;
    public $creatorName;
    public bool $enableUnsavedWarning = true; // Enable onUnload warning if unsaved changes

    /**
     * @var array<string, non-empty-list<string>>|null
     */
    public array|null $supportedTranslations = [];

    public function toJson(): string
    {
        return parent::toJson();
    }
}
