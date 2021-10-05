<?php

namespace App\Traits;

use Log;
use App\Http\Requests\LTIRequest;
use Cerpus\MetadataServiceClient\Exceptions\MetadataServiceException;
use Cerpus\MetadataServiceClient\Contracts\MetadataServiceContract as MetadataService;

trait Taggable
{
    private $metaId = false;

    /**
     * @param array $tags An array of tags you would like to add. Ex: ['tag1', 'tag2']
     * @return $this
     * @throws MetadataServiceException
     */
    public function updateMetaTags($tags = [])
    {
        try {
            $metadataService = $this->getMetadataService();
            $metadataService->setEntityType(MetadataService::ENTITYTYPE_LEARNINGOBJECT);
            $metadataService->setEntityId($this->id);

            $currentMetaTags = collect($this->getMetaTags());

            // Add keywords that are not already in metadataservice
            $addKeywords = collect($tags)->reject(function ($kw) use ($currentMetaTags) {
                return $currentMetaTags->contains('keyword', '==', $kw);
            })->map(function ($keyword) {
                return (object)['keyword' => $keyword];
            })->toArray();

            // If metadataservice has some keywords we do not have, we must remove them,
            // Last one to save wins if there are concurrent updates.
            $removeKeywordIds = $currentMetaTags->reject(function ($kw) use ($tags) {
                return in_array($kw->keyword, $tags);
            });

            $metadataService->createDataFromArray([MetadataService::METATYPE_KEYWORDS => $addKeywords]);
            Log::debug(__METHOD__ . ' Added keywords', $addKeywords);

            $removeKeywordIds->each(function ($kw) use ($metadataService) {
                $metadataService->deleteData(MetadataService::METATYPE_KEYWORDS, $kw->id);
                Log::debug(__METHOD__ . ": Deleted keyword '$kw->keyword' ($kw->id) from metadataservice");
            });
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': Unable to set keywords: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * @return array Tag struct/objects from MetadataService, may be an empty array
     */
    public function getMetaTags(): array
    {
        $tags = [];
        try {
            $metadataService = $this->getMetadataService();
            $metadataService->setEntityType(MetadataService::ENTITYTYPE_LEARNINGOBJECT);
            $metadataService->setEntityId($this->id);

            $tags = $metadataService->getData(MetadataService::METATYPE_KEYWORDS) ?? [];
        } catch (\Exception $e) {
            $request = request();
            /** @var LTIRequest $lti */
            $lti = LTIRequest::current();
            $info = [];

            try {
                $info = [
                    "method" => $request ? $request->method() : "-",
                    "path" => $request ? $request->path() : "-",
                    "id" => $this->id ?? "-",
                    "title" => $this->getContentTitle(),
                    "lti_tool_consumer" => $lti ? $lti->getToolConsumerInfoProductFamilyCode() : "-",
                    "lti_ext_context_id" => $lti ? $lti->getExtContextId() : "-",
                    "lti_ext_activity_id" => $lti ? $lti->getExtActivityId() : "-",
                ];
            } catch (\Throwable $t) {
                Log::error(__METHOD__ . ": Unable to make info: ({$t->getCode()}) {$t->getMessage()}", $info);
            }

            Log::error(__METHOD__ . ": Unable to get tags. ({$e->getCode()}) {$e->getMessage()} ", $info);
        }

        // Check if we have a local copy of the tags of metadataservice comes up empty
        if (empty($tags)) {
            $tags = collect(explode(',', $this->tags))
                ->filter(function ($tag) {
                    return !empty($tag);
                })
                ->map(function ($kw) {
                    return (object)['keyword' => $kw];
                })->toArray();
        }

        return $tags;
    }

    /**
     * @return array Keywords from MetadataService, may be an empty array
     */
    public function getMetaTagsAsArray(): array
    {
        $metaTags = $this->getMetaTags();

        $keywords = collect($metaTags)->map(function ($kw) {
            return $kw->keyword;
        })->toArray();

        return $keywords;
    }

    /**
     * @return string Keywords from MetadataService as a comma separated string.
     */
    public function getMetaTagsAsString(): string
    {
        $tags = $this->getMetaTagsAsArray();

        return trim(implode(',', $tags));
    }

    /**
     * @param array $tags Your own known tags. Example: ['my tag', 'my second tag']
     * @return array the union of your local tags and the keywords from the MetadataService
     */
    public function syncWithMetadataTags($tags = []): array
    {
        $metaTags = $this->getMetaTagsAsArray();

        $mergedKeywords = array_merge($metaTags, $tags);

        return array_unique($mergedKeywords);
    }

    /**
     * @param array $tags Your own known tags. Example: ['my tag', 'my second tag']
     * @return string The union of your local keywords and the keywords from the MetadataService as a comma separated string
     */
    public function syncWithMetadataTagsAsString($tags = []): string
    {
        $syncedTags = $this->syncWithMetadataTags($tags);

        return trim(implode(',', $syncedTags));
    }

    private function getMetadataService(): MetadataService
    {
        try {
            $meta = resolve(MetadataService::class);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' Unable to resolve MetadataService. ' . $e->getMessage());
        }

        return $meta;
    }
}
