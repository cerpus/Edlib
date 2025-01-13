<?php

namespace App\Libraries\H5P\Traits;

use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use App\Libraries\H5P\Packages\H5PBase;
use App\Libraries\HTMLPurify\Config\MathMLConfig;
use Illuminate\Support\Collection;

use function collect;
use function is_array;
use function is_object;

trait H5PCommonAdapterTrait
{
    protected $config;

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

    private function traverseParameters(Collection $values, H5PAlterParametersSettingsDataObject $settings): Collection
    {
        return $values->map(function ($value) use ($settings) {
            if ($this->isImageTarget($value)) {
                $value = $this->imageAdapter->alterImageProperties($value, $settings);
            }
            if ((bool) (array) $value && (is_array($value) || is_object($value))) {
                return $this->traverseParameters(collect($value), $settings);
            }

            return $value;
        });
    }

    private function isImageTarget($value): bool
    {
        if (!$this->imageAdapter instanceof H5PExternalProviderInterface) {
            return false;
        }

        return is_object($value) && !empty($value->mime) && !empty($value->path) && $this->imageAdapter->isTargetType($value->mime, $value->path);
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
            ],
        ];
    }

    public function adapterIs($adapter)
    {
        return strtolower($this->getAdapterName()) === strtolower($adapter);
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
            'iframe *(*)[class,data-*,allow,allowfullscreen];',
        ]);
    }
}
