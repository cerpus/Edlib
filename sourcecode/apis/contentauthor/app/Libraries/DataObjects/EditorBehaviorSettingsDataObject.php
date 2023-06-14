<?php

namespace App\Libraries\DataObjects;

use JsonSerializable;
use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static EditorBehaviorSettingsDataObject create($attributes = null)
 */
class EditorBehaviorSettingsDataObject extends BaseDataObject implements JsonSerializable
{
    use CreateTrait;

    /**
     * Setting to display "Text overrides and translations".
     * True = hide in editor(default)
     * False = show in editor
     *
     * @var bool
     */
    public $hideTextAndTranslations = true;

    private $behaviorSettings;

    public static $rules = [
        'hideTextAndTranslations' => 'boolean',
    ];

    public function setBehaviorSettings(BehaviorSettingsDataObject $settingsDataObject)
    {
        $this->behaviorSettings = $settingsDataObject;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
