<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\H5Plugin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ContentUpgradeController extends Controller
{
    /** @var \H5PCore $core */
    protected $h5pPlugin, $core, $interface;

    /**
     * Upgrades content
     *
     * @return \Illuminate\Http\Response
     */
    public function upgrade(\H5PCore $core, $libraryId)
    {
        $this->core = $core;
        $configuration = new \stdClass();
        try {
            $plugin = H5Plugin::get_instance(DB::connection()->getPdo());
            $interface = $core->h5pF;

            $library = (object)$interface->loadLibraryInfo($libraryId);

            $configuration = $this->getUpgradeConfiguration($library);

            $config = resolve(AdminConfig::class);
            $config->h5plugin = $plugin;
            $config->getConfig();
            $config->addUpdateScripts();

        } catch (\Exception $e) {
            Log::error(__METHOD__ . ". Trying to upgrade content for library id '$libraryId'. Got exception " . $e->getMessage() . $e->getTraceAsString());
            abort(404);
        }
        return view('admin/content-upgrade', [
            'contentTitle' => $library->title,
            'h5pAdminIntegration' => json_encode($configuration),
            'h5pIntegration' => json_encode($config->config),
            'scripts' => $config->getScriptAssets(),
            'styles' => $config->getStyleAssets(),
        ]);
    }

    protected function getCurrentLibraries($libraries = [])
    {
        $currentLibs = [];
        foreach ($libraries as $library) {
            if (!property_exists($library, 'isOld')) {
                $currentLibs[] = $library;
            }
        }
        return $currentLibs;
    }

    protected function getOldLibraries($libraries = [])
    {
        $oldLibs = [];
        foreach ($libraries as $library) {
            if (property_exists($library, 'isOld') && ($library->isOld === true)) {
                $oldLibs[] = $library;
            }
        }
        return $oldLibs;
    }

    protected function getContentForLibraries($libraries = [])
    {
        $oldContent = [];
        $pdo = DB::connection()->getPdo();
        $sql = "select c.id, c.title from h5p_contents as c join h5p_libraries as l on l.id = c.library_id  where library_id=:libraryId";
        $stmt = $pdo->prepare($sql);
        foreach ($libraries as $library) {
            $params = [
                ':libraryId' => $library->id
            ];
            $stmt->execute($params);
            $res = $stmt->fetchAll(\PDO::FETCH_OBJ);
            $library->content = $res;
            $oldContent[$library->name] = $library;
        }
        return $oldContent;
    }

    protected function getUpgradeConfiguration($library)
    {

        $library->id = $library->libraryId;
        $interface = $this->core->h5pF;

        $sql = "SELECT hl2.id, hl2.name, hl2.title, hl2.major_version, hl2.minor_version, hl2.patch_version
          FROM h5p_libraries hl1
          JOIN h5p_libraries hl2
            ON hl2.name = hl1.name
          WHERE hl1.id = :libraryId
          ORDER BY hl2.title ASC, hl2.major_version ASC, hl2.minor_version ASC";
        $params = [
            ':libraryId' => $library->id
        ];

        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $versions = $stmt->fetchAll(\PDO::FETCH_OBJ);

        foreach ($versions as $version) {
            if ($version->id === $library->id) {
                $upgrades = $this->core->getUpgrades($version, $versions);
                break;
            }
        }

        if (count($versions) < 2) {
            return NULL;
        }

        // Get num of contents that can be upgraded
        $contents = $interface->getNumContent($library->id);
        if (!$contents) {
            return NULL;
        }

        return array(
            'containerSelector' => '#h5p-admin-container',
            'libraryInfo' => array(
                'message' => sprintf('You are about to upgrade %s(version %s.%s). Please select upgrade version.', $library->title, $library->majorVersion, $library->minorVersion),
                'inProgress' => 'Upgrading to %ver...', $library->title,
                'error' => 'An error occurred while processing parameters:', $library->title,
                'errorData' => 'Could not load data for library %lib.', $library->title,
                'errorContent' => 'Could not upgrade content %id:', $library->title,
                'errorScript' => 'Could not load upgrades script for %lib.', $library->title,
                'errorParamsBroken' => 'Parameters are broken.', $library->title,
                'done' => 'You have successfully upgraded ' . $library->title,
                'library' => array(
                    'name' => $library->machineName,
                    'version' => $library->majorVersion . '.' . $library->minorVersion,
                ),
                'libraryBaseUrl' => route('content-upgrade-library', ['library' => '']),
                'scriptBaseUrl' => '/h5p-php-library/js',
                'buster' => '?ver=11234',
                'versions' => $upgrades,
                'contents' => $contents,
                'buttonLabel' => 'Upgrade', $library->title,
                'infoUrl' => route('admin.content-upgrade', ['id' => $library->id]),
                'total' => $contents,
                'token' => csrf_token()
            )
        );
    }
}
