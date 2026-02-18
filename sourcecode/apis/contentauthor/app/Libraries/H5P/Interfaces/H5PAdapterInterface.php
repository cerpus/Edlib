<?php

namespace App\Libraries\H5P\Interfaces;

use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;

interface H5PAdapterInterface
{
    /**
     * @param string $parameters JSON parameters
     * @return string modified JSON parameters
     */
    public function alterParameters(
        string $parameters,
        H5PAlterParametersSettingsDataObject $settings = new H5PAlterParametersSettingsDataObject(),
    ): string;

    /**
     * @param object $field
     */
    public function getEditorExtraTags($field): array;


    public function getEditorCss(): array;


    public function getEditorSettings(): array;


    public function getCustomEditorScripts(): array;


    public function getCustomViewScripts(): array;


    public function getCustomViewCss(): array;

    /**
     * @return void
     */
    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion);

    public function getAdapterName(): string;

    /**
     * @return void
     */
    public function overrideAdapterSettings();

    /**
     * @return array
     */
    public static function getAllAdapters();

    /**
     * @return boolean
     */
    public function adapterIs($adapter);

    public function useEmbedLink(): int;

    public static function getCoreExtraTags(): array;

    /** @return void */
    public function setConfig(ConfigInterface $config);

    public function enableEverybodyIsCollaborators(): bool;

    public function useMaxScore(): bool;

    public function addTrackingScripts(): ?string;

    public function getConfigJs(): array;

    public function getCustomEditorStyles(): array;

    public function filterEditorScripts(): array;
}
