<?php

namespace App\Libraries\H5P\Traits;

use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use App\Libraries\H5P\Packages\H5PBase;
use App\Libraries\HTMLPurify\Config\MathMLConfig;

trait H5PCommonAdapterTrait
{
    protected $adapterName;

    protected $config;

    public function hasVideoLibrary(array $scripts, int $minimumMajorVersion = 1, int $minimumMinorVersion = 1)
    {
        return collect($scripts)
            ->filter(function ($library) use ($minimumMajorVersion, $minimumMinorVersion) {
                if ($library['machineName'] === 'H5P.Video') {
                    return intval($library['majorVersion']) >= $minimumMajorVersion && intval($library['minorVersion']) >= $minimumMinorVersion;
                }
                return false;
            })
            ->isNotEmpty();
    }

    public function hasCerpusVideoLibrary(array $scripts, int $minimumMajorVersion = 1, int $minimumMinorVersion = 0)
    {
        return collect($scripts)
            ->filter(function ($library) use ($minimumMajorVersion, $minimumMinorVersion) {
                if ($library['machineName'] === 'H5P.CerpusVideo') {
                    return intval($library['majorVersion']) >= $minimumMajorVersion && intval($library['minorVersion']) >= $minimumMinorVersion;
                }
                return false;
            })
            ->isNotEmpty();
    }

    public function alterPackageSemantics(&$semantics, $machineName)
    {
        try {
            if (!is_null($machineName)) {
                /** @var H5PBase $library */
                $library = H5PPackageProvider::make($machineName);
                $library->alterSemantics($semantics);
            }
        } catch (UnknownH5PPackageException $exception) {
        }
    }

    public function getAdapterName()
    {
        return $this->adapterName;
    }

    public static function getAllAdapters()
    {
        return [
            [
                'name' => 'Cerpus',
                'key' => 'cerpus',
            ],
            [
                'name' => 'NDLA',
                'key' => 'ndla',
            ]
        ];
    }

    public function adapterIs($adapter)
    {
        return strtolower($this->adapterName) === strtolower($adapter);
    }

    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public static function getCoreExtraTags(): array
    {
        return array_merge(MathMLConfig::getMathTags(), [
            'u',
            'sub',
            'sup',
            'strike',
            'span',
            'section',
            'aside',
            'header',
            'p',
            'div',
            'h2',
            'ol',
            'ul',
            'li',
            'embed',
            '*(*)[class,data-*];',
            'iframe *(*)[class,data-*,allow,allowfullscreen];'
        ]);
    }
}
