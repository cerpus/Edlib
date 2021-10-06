<?php


namespace App\Traits;


use Cerpus\MetadataServiceClient\Contracts\MetadataServiceContract as MetadataService;
use Illuminate\Support\Facades\Log;

trait CopiesCustomMetadataFields
{
    private function copyCustomFieldsMetadata($oldId, $newId)
    {
        if (is_null($oldId) || is_null($newId) || $newId === $oldId) {
            return;
        }

        try {
            /** @var MetadataService $metadataService */
            $metadataService = app(MetadataService::class);
            $oldCustomFields = $metadataService->setEntityId($oldId)->fetchAllCustomFields();

            // Create new learning object if the new one does not yet exist
            $metadataService->setEntityId($newId)->getLearningObject(true);

            foreach ($oldCustomFields as $customField) {
                if ($customField->name !== 'published') {  // Handled elsewhere.
                    // Assume we don't want duplicate values
                    $metadataService->setEntityId($newId)->setCustomFieldValue($customField->name, $customField->value, true);
                }
            }
        } catch (\Throwable $t) {
            Log::error(__METHOD__ . ': (' . $t->getCode() . ') ' . $t->getMessage());
        }
    }
}
