<?php

namespace App\Libraries\NDLA\Exporters\Transformers;

use App\NdlaIdMapper;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use App\Libraries\NDLA\API\ImageApiClient;

class SubjectTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'courses'
    ];

    protected $imageApi;

    public function __construct()
    {
        $this->imageApi = resolve(ImageApiClient::class);
    }

    public function transform($subject): array
    {
        $imageUrl = $subject->subjectpage->banner->desktopUrl ?? '';
        $imageType = '';
        if ($imageUrl) {
            $imageId = last(explode('/', $imageUrl));
            if ($metaData = $this->imageApi->fetchMetaData($imageId)) {
                $imageType = $metaData->contentType ?? 'If you see this there is a bug in the CourseTransformer.';
            }
        }

        return [
            'user' => config('ndla.userId'),
            'collaborators' => $this->getCollaborators(),
            'id' => $subject->id ?? 'unknown',
            'title' => $subject->name ?? "Missing subject name",
            'intro' => $subject->subjectpage->metaDescription ?? $subject->subjectpage->about->description ?? '',
            'image' => $imageUrl,
            'image_type' => $imageType,
        ];
    }

    public function includeCourses($subject): Collection
    {
        return $this->collection(($subject->topics ?? []), new CourseTransformer($subject));
    }

    protected function getCollaborators(): array
    {
        $collaborators = [];
        if (!empty(config('ndla.export.collaborators'))) {
            $collaborators = explode(',', str_replace(' ', '', config('ndla.export.collaborators')));
        }

        return $collaborators;
    }
}
