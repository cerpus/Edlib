<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\H5PLibrary;
use App\Libraries\H5P\AdminConfig;
use H5PCore;
use H5PFrameworkInterface;
use Illuminate\Contracts\View\View;

final class ContentUpgradeController
{
    public function __construct(
        private readonly H5PCore $core,
        private readonly H5PFrameworkInterface $framework,
    ) {}

    public function upgrade(H5PLibrary $library): View
    {
        $configuration = $this->getUpgradeConfiguration($library);

        $config = resolve(AdminConfig::class);
        $config->getConfig();
        $config->addUpdateScripts();

        return view('admin/content-upgrade', [
            'contentTitle' => $library->title,
            'h5pAdminIntegration' => $configuration,
            'h5pIntegration' => $config->config,
            'scripts' => $config->getScriptAssets(),
            'styles' => $config->getStyleAssets(),
        ]);
    }

    private function getUpgradeConfiguration(H5PLibrary $library): array|null
    {
        $versions = H5PLibrary::where('name', $library->name)
            ->orderBy('title')
            ->orderBy('major_version')
            ->orderBy('minor_version')
            ->get()
            ->all();

        $upgrades = $this->core->getUpgrades($library, $versions);

        if (count($versions) < 2) {
            // TODO: what is the resulting behaviour when returning NULL?
            return null;
        }

        $upgradableContentCount = $this->framework->getNumContent($library->id);
        if ($upgradableContentCount < 1) {
            return null;
        }

        return [
            'containerSelector' => '#h5p-admin-container',
            'libraryInfo' => [
                'message' => sprintf(
                    'You have selected to upgrade %s resource(s) of type "%s" from version %s.%s.%s.<br>Which version of "%s" do you want to upgrade to?',
                    $upgradableContentCount,
                    $library->title,
                    $library->major_version,
                    $library->minor_version,
                    $library->patch_version,
                    $library->title,
                ),
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
                'contents' => $upgradableContentCount,
                'buttonLabel' => 'Start upgrade',
                'infoUrl' => route('admin.content-upgrade', ['id' => $library->id]),
                'total' => $upgradableContentCount,
                'token' => csrf_token(),
            ],
        ];
    }
}
