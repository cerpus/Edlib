<?php

namespace App\Libraries\H5P;

use App\H5PContent;
use App\Libraries\H5P\Interfaces\H5PExternalProviderInterface;
use H5PContentValidator;
use H5PCore;
use H5PExport as H5PDefaultExport;
use Illuminate\Support\Collection;
use JsonException;
use UnexpectedValueException;

use function json_decode;
use function property_exists;

use const JSON_THROW_ON_ERROR;

readonly class H5PExport
{
    /**
     * @param array<array-key, object> $externalProviders
     */
    public function __construct(
        private H5PDefaultExport $export,
        private H5PContentValidator $validator,
        private bool $convertMediaToLocal,
        private array $externalProviders,
    ) {}

    /**
     * @throws JsonException
     */
    public function generateExport(H5PContent $content): bool
    {
        if ($this->convertMediaToLocal) {
            $parameters = $this->storeContentToDisk($content);
        } else {
            $parameters = $content->parameters;
        }

        $library = H5PCore::libraryFromString($content->library->getLibraryString(false))
            ?: throw new UnexpectedValueException('Bad library string');
        $library['libraryId'] = $content->library->id;
        $library['name'] = $content->library->name;

        $contents = $content->toArray();
        $contents['params'] = $parameters;
        $contents['embedType'] = $content->embed_type;
        $contents['library'] = $library;
        $contents['metadata'] = $content->getMetadataStructure();
        $contents['filtered'] = $parameters;

        // we don't use the so-called validator for purposes other than
        // resolving dependencies, as it likes to corrupt the data

        $validatorParams = (object) [
            'library' => $content->library->getLibraryString(false),
            'params' => json_decode($content->parameters),
        ];
        $this->validator->validateLibrary($validatorParams, (object) [
            'options' => [$validatorParams->library],
        ]);
        $contents['dependencies'] = $this->validator->getDependencies();

        return $this->export->createExportFile($contents);
    }

    private function storeContentToDisk(H5PContent $content): string
    {
        $parameters = json_decode($content->parameters, flags: JSON_THROW_ON_ERROR);
        $filtered = $this->traverseParameters(collect($parameters), $content);

        return $filtered->toJson();
    }

    private function traverseParameters(Collection $parameters, H5PContent $content): Collection
    {
        return $parameters->map(function (mixed $value) use ($content) {
            if (is_object($value) && property_exists($value, 'mime') && !empty($value->path) && !$this->isPathLocal($value->path)) {
                $value = $this->applyExternalProviderHandling((array) $value, $content);
            }

            if (is_array($value) || is_object($value)) {
                $value = $this->traverseParameters(collect($value), $content);
            }

            return $value;
        });
    }

    private function applyExternalProviderHandling(array $values, H5PContent $content): array
    {
        $externalProvider = collect($this->externalProviders)
            ->first(
                fn($provider) =>
                $provider instanceof H5PExternalProviderInterface &&
                $provider->isTargetType($values['mime'], $values['path']),
            );

        if ($externalProvider instanceof H5PExternalProviderInterface) {
            $fileDetails = $externalProvider->storeContent($values, $content);
            $values = array_merge($values, $fileDetails);
        }

        return $values;
    }

    private function isPathLocal(string $path): bool
    {
        return preg_match('!^(images|videos|audios|files)/!', $path);
    }
}
