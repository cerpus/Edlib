<?php

namespace App\Libraries\H5P;

use App\Exceptions\H5pImportException;
use App\Libraries\H5P\Dataobjects\H5PImportDataObject;
use App\Libraries\H5P\Dataobjects\H5PMetadataObject;
use H5PStorage;
use Illuminate\Http\UploadedFile;
use LogicException;

class H5PImport
{
    private $editorAjax;

    public function __construct(\H5PEditorAjax $editorAjax)
    {
        $this->editorAjax = $editorAjax;
    }

    /**
     * @return H5PImportDataObject|bool
     * @throws \Exception
     */
    public function import(UploadedFile $uploadedFile, H5PStorage $storage, $userId, bool $isDraft = null, bool $isPrivate = null)
    {
        $core = $this->editorAjax->core;
        $file = $this->saveFileTemporarily($uploadedFile);
        if (!$file) {
            throw new LogicException("Could not save file");
        }

        if (!$this->isPackageValid()) {
            throw new H5pImportException(implode('; ', $core->h5pF->getMessages('error')));
        }

        $displayOptions = $core->getDisplayOptionsForEdit();
        $metadata = collect($core->mainJsonData)
            ->whenEmpty(function ($metadata) {
                return $metadata->put(['license' => "U"]);
            })
            ->only(H5PMetadataObject::H5PMetadataFieldsInOrder);
        $content = array_merge($core->mainJsonData, [
            'metadata' => $metadata->toArray(),
            'embed_type' => $core->mainJsonData['embedTypes'][0],
            'disable' => $core->getStorableDisplayOptions($displayOptions, null),
            'user_id' => $userId,
            'max_score' => null,
            'slug' => \H5PCore::slugify($core->mainJsonData['title']),
        ]);

        // Install any required dependencies
        $storage->savePackage($content, null, false);
        if (empty($storage->contentId)) {
            throw new LogicException("Can't find the id. Whaaat?");
        }
        $content['id'] = $storage->contentId;

        // Clean up
        $this->editorAjax->storage->removeTemporarilySavedFiles($core->h5pF->getUploadedH5pFolderPath());

        return H5PImportDataObject::create($content['id'], $content['mainLibrary'], $content['title']);
    }

    private function saveFileTemporarily(UploadedFile $uploadedFile)
    {
        return $this->editorAjax->storage->saveFileTemporarily($uploadedFile->path(), true);
    }

    private function isPackageValid()
    {
        $validator = resolve(\H5PValidator::class);
        if (!$validator->isValidPackage(false, false)) {
            $this->editorAjax->storage->removeTemporarilySavedFiles($this->editorAjax->core->h5pF->getUploadedH5pPath());
            return false;
        }
        return true;
    }
}
