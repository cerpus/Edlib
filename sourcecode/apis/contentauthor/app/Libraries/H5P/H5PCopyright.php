<?php

namespace App\Libraries\H5P;

use App\H5PContent;
use App\H5PContentsMetadata;
use App\Libraries\H5P\Dataobjects\H5PCopyrightAuthorDataObject;
use App\Libraries\H5P\Dataobjects\H5PCopyrightDataObject;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use Illuminate\Support\Collection;
use JsonException;

use const JSON_THROW_ON_ERROR;

class H5PCopyright
{
    /** @var H5PContent */
    private $h5pContent;
    private $assetCopyright;
    private $h5pCopyright;
    private $h5pCore;
    /** @var \H5PDefaultStorage|\H5PFileStorage|CerpusStorageInterface  */
    private $storage;

    public function __construct(\H5PCore $core)
    {
        $this->assetCopyright = collect();
        $this->h5pCore = $core;
        $this->storage = $core->fs;
    }

    /**
     * @return array
     * @throws JsonException
     */
    public function getCopyrights(H5PContent $content)
    {
        $this->h5pContent = $content;
        $this->contentCopyright();
        if (empty($content->filtered)) {
            $contentCopy = $content->toArray();
            $contentCopy['params'] = $contentCopy['parameters'];
            $contentCopy['library'] = $content->library->getLibraryH5PFriendly();
            $content->filtered = $this->h5pCore->filterParameters($contentCopy);
        }
        $filtered = json_decode($content->filtered, true, flags: JSON_THROW_ON_ERROR);
        $this->traverseFiltered(collect($filtered));

        return [
            'h5p' => !is_null($this->h5pCopyright) ? $this->h5pCopyright->toArray() : null,
            'h5pLibrary' => $content->library ? [
                'name' => $content->library->name,
                'majorVersion' => $content->library->major_version,
                'minorVersion' => $content->library->minor_version,
            ] : null,
            'assets' => $this->assetCopyright->toArray(),
        ];
    }

    private function contentCopyright()
    {
        if ($this->hasContentMetadata()) {
            /** @var H5PContentsMetadata $metadata */
            $metadata = $this->h5pContent->metadata()->first();
            $metadata->authors = json_decode($metadata->authors);
            $copyright = H5PCopyrightDataObject::create($metadata->convertToMetadataObject($this->h5pContent->title)->toArray());
            $copyright->contentType = 'H5P';
            $this->h5pCopyright = $copyright;
        }
    }

    private function traverseFiltered($values)
    {
        /** @var Collection $values */
        $values->each(function ($value) {
            if ($this->hasCopyright($value)) {
                $this->addFromCopyright($value);
            } elseif ($this->hasMetadata($value)) {
                $this->addFromMetadata($value);
            }
            $this->specialHandling($value);
            if (is_array($value)) {
                $this->traverseFiltered(collect($value));
            }
        });
    }

    private function hasCopyright($field): bool
    {
        return !empty($field['copyright']) &&
            is_array($field['copyright']) &&
            $this->hasLicense($field['copyright']);
    }

    private function hasMetadata($field): bool
    {
        return !empty($field['metadata']) &&
            is_array($field['metadata']) &&
            $this->hasLicense($field['metadata']);
    }

    private function hasLicense($field): bool
    {
        return array_key_exists('license', $field) && $field['license'] !== 'U';
    }

    private function hasContentMetadata(): bool
    {
        $contentMetadata = $this->h5pContent->metadata()->first();
        if (!is_null($contentMetadata) && $this->hasLicense($contentMetadata->toArray())) {
            return true;
        }
        return false;
    }

    private function addFromCopyright($values)
    {
        $copyrightValues = $values['copyright'];
        $copyright = [];
        foreach ([
            'title' => 'title',
            'license' => 'license',
            'licenseVersion' => 'version',
            'authors' => 'author',
            'source' => 'source',
            'years' => 'year',
        ] as $index => $field) {
            if (!array_key_exists($field, $copyrightValues)) {
                continue;
            }
            $copyright[$index] = $copyrightValues[$field];
        }

        if (!empty($values['mime'])) {
            if ($this->isImage($values['mime'])) {
                $copyright['contentType'] = "Image";
                $copyright['thumbnail'] = $this->storage->getContentPath($this->h5pContent->id, $values['path']);
            } elseif ($this->isVideo($values['mime'])) {
                $copyright['contentType'] = "Video";
            }
        }

        if (!empty($copyright['authors']) && !is_array($copyright['authors'])) {
            $copyright['authors'] = [H5PCopyrightAuthorDataObject::create([
                'name' => $copyright['authors'],
            ])->toArray()];
        }

        if (!empty($copyright['years'])) {
            $years = explode("-", $copyright['years']);
            $copyright['yearFrom'] = $years[0];
            if (!empty($years[1])) {
                $copyright['yearTo'] = $years[1];
            }
            unset($copyright['years']);
        }

        $metadata = H5PCopyrightDataObject::create($copyright);
        $this->assetCopyright->push($metadata->toArray());
    }

    private function isImage($mime)
    {
        return !empty($mime) && strpos($mime, 'image/') === 0;
    }

    private function isVideo($mime)
    {
        return !empty($mime) && strpos($mime, 'video/') === 0;
    }

    private function addFromMetadata($values)
    {
        $metadata = $values['metadata'];
        $copyright = [];
        foreach ([
            'title',
            'authors',
            'source',
            'yearFrom',
            'yearTo',
            'license',
            'licenseVersion',
            'licenseExtra',
            'contentType',
        ] as $field) {
            if (!array_key_exists($field, $metadata)) {
                continue;
            }
            $copyright[$field] = $metadata[$field];
        }

        if (!empty($values['params']) && !empty($values['params']['file']) && $values['params']['contentName'] === 'Image') {
            $copyright['thumbnail'] = $this->storage->getContentPath($this->h5pContent->id, $values['params']['file']['path']);
        }
        $copyrightDataObject = H5PCopyrightDataObject::create($copyright);
        $this->assetCopyright->push($copyrightDataObject->toArray());
    }

    private function specialHandling($values)
    {
        if ($this->h5pContent->library && in_array($this->h5pContent->library->name, ['H5P.CerpusImage', 'H5P.CerpusVideo'])) {
            if (is_array($values) && !empty($values['mime'])) {
                if ($this->isImage($values['mime'])) {
                    $this->h5pCopyright->contentType = "Image";
                    $this->h5pCopyright->thumbnail = $this->storage->getContentPath($this->h5pContent->id, $values['path']);
                } elseif ($this->isVideo($values['mime'])) {
                    $this->h5pCopyright->contentType = "Video";
                }
            }
        }
    }
}
