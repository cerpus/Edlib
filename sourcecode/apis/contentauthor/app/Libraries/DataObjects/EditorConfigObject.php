<?php


namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static EditorConfigObject create($attributes = null)
 */
class EditorConfigObject
{
    use CreateTrait;

    public $userPublishEnabled, $canPublish, $canList, $useLicense = false;

    protected $contentProperties;

    public $locked = false;
    public $pulseUrl = null;
    public $editorLanguage;

    protected $lockedProperties;

    public function setContentProperties(ResourceInfoDataObject $infoDataObject) {
        $this->contentProperties = $infoDataObject->toArray();
    }

    public function setLockedProperties(LockedDataObject $lockedDataObject) {
        $this->locked = true;
        $this->lockedProperties = $lockedDataObject->toArray();
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
