<?php

namespace App\Libraries\H5P\Packages;

use App\Libraries\DataObjects\BehaviorSettingsDataObject;
use App\Libraries\DataObjects\EditorBehaviorSettingsDataObject;
use App\Libraries\H5P\Interfaces\ContentTypeInterface;
use App\Libraries\H5P\Interfaces\PackageInterface;
use App\Traits\H5PBehaviorSettings;

/**
 * @method applyEditorBehaviorSettings(EditorBehaviorSettingsDataObject $settingsDataObject)
 * @method applyBehaviorSettings(BehaviorSettingsDataObject $settingsDataObject)
 * @method getPackageStructure($asJson = false)
 * @method addCss($css);
 * @method getCss($asString = false);
 */
abstract class H5PBase implements PackageInterface, ContentTypeInterface
{
    use H5PBehaviorSettings;

    public static string $machineName;
    public static int $majorVersion;
    public static int $minorVersion;
    protected $answers;
    protected bool $composedComponent = false;
    protected $elements;
    protected bool $canExtractAnswers = true;
    protected $semantics;

    public function __construct(string $packageStructure = null)
    {
        if (!is_null($packageStructure)) {
            $this->packageStructure = json_decode($packageStructure);
        }
        $this->semantics = $this->getPackageSemantics();
    }

    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    public function isComposedComponent(): bool
    {
        return $this->composedComponent;
    }

    public function canExtractAnswers(): bool
    {
        return $this->canExtractAnswers;
    }

    public function getLibraryWithVersion(): string
    {
        return sprintf("%s %d.%d", static::$machineName, static::$majorVersion, static::$minorVersion);
    }

    public function alterSemantics(&$semantics) {}

    public function alterSource($sourceFile, array $newSource)
    {
        return true;
    }

    public function getSources() {}

    public function validate(): bool
    {
        return false;
    }

    public function getIcon()
    {
        return null;
    }

    /**
     * Make content type specific modifications to H5P config.
     */
    public function alterConfig(object $config): object
    {
        // Override in package in "Libraries\H5P\Packages\*" if you need to modify the default.
        return $config;
    }
}
