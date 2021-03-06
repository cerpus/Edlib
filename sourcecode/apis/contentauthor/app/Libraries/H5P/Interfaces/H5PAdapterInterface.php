<?php

namespace App\Libraries\H5P\Interfaces;


use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use Illuminate\Support\Collection;

interface H5PAdapterInterface
{
    /**
     * Alter parameters before added to the H5PIntegrationObject
     *
     * @param string $parameters
     * @param H5PAlterParametersSettingsDataObject|null $settings
     * @return string
     */
    public function alterParameters($parameters, H5PAlterParametersSettingsDataObject $settings = null);

    /**
     * @param object $field
     * @return array
     */
    public function getEditorExtraTags($field): array;

    /**
     * @return array
     */
    public function getEditorCss(): array;

    /**
     * @return array
     */
    public function getEditorSettings(): array;

    /**
     * @return array
     */
    public function getCustomEditorScripts(): array;

    /**
     * @return array
     */
    public function getCustomViewScripts(): array;

    /**
     * @return array
     */
    public function getCustomViewCss(): array;

    /**
     * @param $semantics
     * @return void
     */
    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion);

    /**
     * @return string|null
     */
    public function getAdapterName();

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
    public function getDefaultImportPrivacy();

    /**
     * @return boolean
     */
    public function adapterIs($adapter);

    public function useEmbedLink(): int;

    /** @return bool */
    public function emptyArticleImportLog($sessionKey): void;

    /** @return bool */
    public function resetNdlaIdTracking($sessionKey): void;

    /** @return bool */
    public function showArticleImportExportFunctionality(): bool;

    /** @return void */
    public function runPresaveCommand(): void;

    static public function getCoreExtraTags(): array;

    /** @return void */
    public function setConfig(ConfigInterface $config);

    public function isUserPublishEnabled(): bool;

    /** @return bool */
    public function enableEverybodyIsCollaborators():bool;

    public function getExternalProviders(): Collection;

    public function useMaxScore(): bool;

    public function autoTranslateTo(): ?string;

    public function addTrackingScripts(): ?string;

    public function getConfigJs(): array;

    public function getCustomEditorStyles(): array;
}
