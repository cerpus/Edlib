<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static EditorConfigObject create($attributes = null)
 */
class EditorConfigObject
{
    use CreateTrait;

    public $canList;
    public $useLicense = false;

    protected $contentProperties;

    /** @var string $editorLanguage IETF code (same as HTML 'lang' attribute), e.g. 'nb-no' for Norwegian Bokmål */
    public $editorLanguage;

    public function setContentProperties(ResourceInfoDataObject $infoDataObject)
    {
        $this->contentProperties = $infoDataObject->toArray();
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
