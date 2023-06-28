<?php

namespace App\Libraries\H5P\Interfaces;

use App\Libraries\DataObjects\BehaviorSettingsDataObject;
use App\Libraries\DataObjects\EditorBehaviorSettingsDataObject;

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
