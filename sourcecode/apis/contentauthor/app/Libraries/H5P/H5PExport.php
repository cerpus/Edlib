<?php

namespace App\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use H5PCore;
use H5PExport as H5PDefaultExport;
use Illuminate\Support\Collection;

class H5PExport
{
    private $content;
    private $export;
    private $adapter;
    private $externalProviders;

    public function __construct(H5PContent $content, H5PDefaultExport $export, H5PAdapterInterface $adapter)
    {
        $this->content = $content;
        $this->export = $export;
        $this->adapter = $adapter;

        $this->externalProviders = $adapter->getExternalProviders();
    }

    public function generateExport($convertMediaToLocal = false)
    {
        if ($convertMediaToLocal && $this->externalProviders->isNotEmpty()) {
            $this->processContent();
        }
        /** @var H5PLibrary $h5PLibrary */
        $h5PLibrary = $this->content->library;
        $library = H5PCore::libraryFromString($h5PLibrary->getLibraryString());
        $library['libraryId'] = $h5PLibrary->id;
        $library['name'] = $h5PLibrary->name;

        $contents = $this->content->toArray();
        $contents['params'] = $this->content->parameters;
        $contents['embedType'] = $this->content->embed_type;
        $contents['library'] = $library;
        $contents['metadata'] = $this->content->getMetadataStructure();

        /** @var \H5PContentValidator $validator */
        $validator = resolve(\H5PContentValidator::class);
        $params = (object)[
            'library' => $h5PLibrary->getLibraryString(),
            'params' => json_decode($this->content->parameters)
        ];
        if (!$params->params) {
            return null;
        }
        $validator->validateLibrary($params, (object)['options' => [$params->library]]);

        // Update content dependencies.
        $contents['dependencies'] = $validator->getDependencies();
        $contents['filtered'] = json_encode($params->params);

        return $this->export->createExportFile($contents);
    }

    private function processContent()
    {
        $content = json_decode($this->content->parameters);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        $filtered = $this->traverseFiltered($this->content, collect($content));

        $this->content->parameters = $filtered->toJson();
    }

    private function traverseFiltered(H5PContent $h5p, $parameters)
    {
        /** @var Collection $parameters */
        $processedParams = $parameters->map(function ($value) use ($h5p) {
            if (!empty($value->mime) && !$this->isPathLocal($value->path)) {
                $value = $this->storeContent((array)$value, $h5p);
            }

            if ((bool)(array)$value && (is_array($value) || is_object($value))) {
                $value = $this->traverseFiltered($h5p, collect($value));
            }
            return $value;
        });
        return $processedParams;
    }

    private function storeContent($values, $content)
    {
        /** @var H5PExternalProviderInterface $externalProvider */
        $externalProvider = $this->externalProviders->first(function ($provider) use ($values) {
            return $provider->isTargetType($values['mime'], $values['path']);
        });

        if (!is_null($externalProvider)) {
            $externalProvider->setStorage($this->export->h5pC->fs);
            $fileDetails = $externalProvider->storeContent($values, $content);
            $values = array_merge($values, $fileDetails);
        }
        return $values;
    }

    private function isPathLocal($path)
    {
        return !empty($path) && preg_match('/^(images|videos|audios|files)\//', $path, $matches);
    }
}
