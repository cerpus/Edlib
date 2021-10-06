<?php

namespace App\Libraries\NDLA\Exporters\Transformers;

use App\NdlaIdMapper;
use League\Fractal\TransformerAbstract;
use App\Libraries\NDLA\API\ImageApiClient;
use App\Libraries\NDLA\Traits\FiltersArticles;

class SubtopicModuleTransformer extends TransformerAbstract
{
    use FiltersArticles;

    /** @var ImageApiClient */
    protected $imageApi;

    public function __construct()
    {
        $this->imageApi = resolve(ImageApiClient::class);
    }

    protected $defaultIncludes = [
        'resources'
    ];

    public function transform($module)
    {
        $imageUrl = $module->article->metaImage->url ?? $module->meta->metaImage->url ?? '';
        $imageType = '';
        if ($imageUrl) {
            $imageId = last(explode('/', $imageUrl));
            if ($imageId && $metaData = $this->imageApi->fetchMetaData($imageId)) {
                $imageType = $metaData->contentType ?? '';
            }
            else {
                $imageUrl = '';
            }
        }
        return [
            'title' => $module->name,
            'image' => $imageUrl,
            'image_type' => $imageType,
            'intro' => $module->article->introduction ?? $module->meta->metaDescription ?? '',
        ];
    }

    public function includeResources($module)
    {
        $resources = array_merge(($module->coreResources ?? []), ($module->supplementaryResources ?? []));

        $importedResources = $this->getImportedResources($resources);

        $importedSubtopicResources = [];

        // Get all subtopics' resources if any.
        foreach ($module->subtopics ?? [] as $subtopic) {
            $subtopicResources = array_merge(($subtopic->coreResources ?? []), ($subtopic->supplementaryResources ?? []));
            foreach ($subtopicResources as $subtopicResource) {
                $subtopicResource->prefix = $subtopic->name . ' - ';
            }
            $importedSubtopicResources = array_merge($importedSubtopicResources, $this->getImportedResources($subtopicResources));
        }

        $allResources = array_merge($importedResources, $importedSubtopicResources);

        $allArticles = $this->filterArticlesAndDeduplicate($allResources);

        return $this->collection($allArticles, new ResourceTransformer);
    }

    /**
     * Given a list of NDLA resources, return the resources already imported into EdLib.
     * @param array $resources
     * @return array
     */
    protected function getImportedResources(array $resources): array
    {
        $importedResources = [];

        foreach ($resources as $resource) {
            if ($idMapper = NdlaIdMapper::articleByNdlaId(last(explode(':', $resource->contentUri ?? 0)))) {
                if ($idMapper->launch_url ?? null) {
                    $resource->launch_url = $idMapper->launch_url;
                    $importedResources[] = $resource;
                }
            }
        }

        return $importedResources;
    }
}
