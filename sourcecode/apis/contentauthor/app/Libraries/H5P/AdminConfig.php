<?php

namespace App\Libraries\H5P;

use App\H5PLibrary;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use App\Libraries\H5P\Traits\Config;
use H5PCore;

class AdminConfig implements ConfigInterface
{
    use Config;

    private $id;

    public function __construct(
        private H5PCore $core,
    ) {
        $this->h5pCore = $core;
        $this->fileStorage = $core->fs;
    }

    /**
     * @return AdminConfig
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getConfig()
    {
        $this->initConfig();
        $this->addCoreAdminAssets();
        return $this->config;
    }

    private function addCoreAdminAssets()
    {
        $core = $this->h5pCore;
        foreach ($core::$adminScripts as $script) {
            $scriptPath = $this->getAssetUrl("core", $script);
            if (!in_array($scriptPath, $this->getScriptAssets())) {
                $this->addAsset("scripts", $scriptPath);
            }
        }
    }

    public function addUpdateScripts($skipCore = false)
    {
        if ($skipCore !== false) {
            $this->addCoreAssets();
        }
        foreach (['js/h5p-version.js', 'js/h5p-content-upgrade.js'] as $script) {
            $scriptPath = $this->getAssetUrl("core", $script);
            if (!in_array($scriptPath, $this->getScriptAssets())) {
                $this->addAsset("scripts", $scriptPath);
            }
        }

        $this->addAsset("styles", $this->getAssetUrl("core", 'styles/h5p-admin.css'));
    }

    public function addPresaveScripts()
    {
        $this->addCoreAssets();
        $this->addAsset('scripts', $this->getAssetUrl('editor', 'scripts/h5peditor-editor.js'));
        $this->addAsset('scripts', $this->getAssetUrl('editor', 'language/en.js'));
        $this->addAsset('scripts', (string) mix('js/maxscore.js'));
        $this->addAsset('scripts', asset('/js/h5p/h5peditor-pre-save.js'));
    }

    public function getSettings(H5PLibrary $library)
    {
        $upgrades = $library->getUpgrades(false)
            ->reduce(function ($old, $new) {
                $old[$new['id']] = $new['version'];
                return $old;
            }, []);
        $contents = $library->contents()->count();

        $settings = [
            'containerSelector' => '#h5p-admin-container',
            'libraryInfo' => [
                'message' => sprintf('You are about to upgrade %s(version %s.%s). Please select upgrade version.', $library->title, $library->majorVersion, $library->minorVersion),
                'inProgress' => 'Upgrading to %ver...',
                'error' => 'An error occurred while processing parameters:',
                'errorData' => 'Could not load data for library %lib.',
                'errorContent' => 'Could not upgrade content %id:',
                'errorLibrary' => 'Missing required library %lib.',
                'errorScript' => 'Could not load upgrades script for %lib.',
                'errorParamsBroken' => 'Parameters are broken.',
                'errorTooHighVersion' => 'Parameters contain %used while only %supported or earlier are supported.',
                'errorNotSupported' => 'Parameters contain %used which is not supported.',
                'done' => 'You have successfully upgraded ' . $library->title,
                'library' => [
                    'name' => $library->name,
                    'version' => $library->major_version . '.' . $library->minor_version,
                ],
                'libraryBaseUrl' => route('content-upgrade-library', ['library' => '']),
                'scriptBaseUrl' => '/h5p-php-library/js',
                'buster' => '?ver=11234',
                'versions' => $upgrades,
                'contents' => $contents,
                'buttonLabel' => 'Upgrade', $library->title,
                'total' => $contents,
                'token' => csrf_token(),
            ],
        ];
        return $settings;
    }

    public function getMaxScoreSettings()
    {
        return [
            'l10n' => $this->getl10n(),
        ];
    }
}
