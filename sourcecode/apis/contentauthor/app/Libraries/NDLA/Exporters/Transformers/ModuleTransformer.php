<?php

namespace App\Libraries\NDLA\Exporters\Transformers;

use App\NdlaIdMapper;
use League\Fractal\TransformerAbstract;
use App\Libraries\NDLA\API\ImageApiClient;
use App\Libraries\NDLA\Traits\FiltersArticles;

class ModuleTransformer extends TransformerAbstract
{
    use FiltersArticles;

    /** @var ImageApiClient */
    protected $imageApi;

    protected $course;

    public function __construct($course = null)
    {
        $this->imageApi = resolve(ImageApiClient::class);
        $this->course = $course;
    }

    protected $defaultIncludes = [
        'resources'
    ];

    public function transform($module)
    {
        $imageUrl = $module->image ?? '';
        $imageType = '';
        if ($imageUrl) {
            $imageId = last(explode('/', $imageUrl));
            if ($imageUrl && $metaData = $this->imageApi->fetchMetaData($imageId)) {
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
            'intro' => $module->intro ?? '',
        ];
    }

    public function includeResources($module)
    {
        $resources = array_merge(($module->coreResources ?? []), ($module->supplementaryResources ?? []));

        $importedResources = [];

        foreach ($resources as $resource) {
            if ($idMapper = NdlaIdMapper::articleByNdlaId(last(explode(':', $resource->contentUri ?? 0)))) {
                if ($idMapper->launch_url ?? null) {
                    $resource->launch_url = $idMapper->launch_url;
                    $importedResources[] = $resource;
                }
            }
        }

        $allArticles = $this->filterArticlesAndDeduplicate($importedResources);

        return $this->collection($allArticles, new ResourceTransformer);
    }
}
