<?php

namespace App\Libraries\H5P\Interfaces;

use Cerpus\CoreClient\DataObjects\BehaviorSettingsDataObject;
use Cerpus\CoreClient\DataObjects\EditorBehaviorSettingsDataObject;

interface ContentTypeInterface
{
    public function getPackageSemantics();

    public function populateSemanticsFromData($data);

    public function alterSemantics(&$semantics);

    public function getIcon();

    public function applyBehaviorSettings(BehaviorSettingsDataObject $settingsDataObject);

    public function applyEditorBehaviorSettings(EditorBehaviorSettingsDataObject $settingsDataObject);

    public function getPackageStructure($asJson = false);

    public function addCss($css);

    public function getCss($asString = false);
}
