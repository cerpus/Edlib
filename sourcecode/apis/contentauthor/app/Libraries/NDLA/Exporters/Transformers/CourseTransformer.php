<?php

namespace App\Libraries\NDLA\Exporters\Transformers;

use App\NdlaIdMapper;
use League\Fractal\TransformerAbstract;
use App\Libraries\NDLA\API\ImageApiClient;

class CourseTransformer extends TransformerAbstract
{

    protected $defaultIncludes = [
        'children'
    ];

    protected $imageApi;

    protected $subject;

    public function __construct($subject = null)
    {
        $this->imageApi = resolve(ImageApiClient::class);
        $this->subject = $subject;
    }


    public function transform($course)
    {
        $imageUrl = $course->meta->metaImage->url ?? '';
        $imageType = '';
        if ($imageUrl) {
            $imageId = last(explode('/', $imageUrl));
            if ($imageId && $metaData = $this->imageApi->fetchMetaData($imageId)) {
                $imageType = $metaData->contentType ?? 'If you see this there is a bug in the CourseTransformer.';
            }
            else {
                $imageUrl = '';
            }
        }

        $title = $course->name;
        if ($this->subject->name ?? null) {
            $title = $this->subject->name . ' - ' . $title;
        }

        $language = $this->getLanguage($course);

        return [
            'user' => config('ndla.userId'),
            'collaborators' => $this->getCollaborators(),
            'id' => $course->id ?? 'unknown',
            'title' => $title,
            'intro' => $course->article->introduction ?? $course->meta->metaDescription ?? '',
            'language' => $language,
            'image' => $imageUrl,
            'image_type' => $imageType,
        ];

    }

    public function includeChildren($course)
    {
        if (!empty($course->subtopics)) {
            // Make modules out of each subtopic
            return $this->collection($course->subtopics, new SubtopicModuleTransformer);
        } else {
            // Make a module out of core and supplementary resources
            $imageUrl = $course->meta->metaImage->url ?? '';

            $module = (object)[
                'name' => $course->name,
                'image' => $imageUrl,
                'intro' => $course->article->introduction ?? ($course->meta->metaDescription ?? ''),
                'coreResources' => $course->coreResources ?? [],
                'supplementaryResources' => $course->supplementaryResources ?? [],
            ];

            return $this->collection([$module], new ModuleTransformer($course));
        }
    }

    protected function getCollaborators()
    {
        $collaborators = [];
        if (!empty(config('ndla.export.collaborators'))) {
            $collaborators = explode(',', str_replace(' ', '', config('ndla.export.collaborators')));
        }

        return $collaborators;
    }

    // Return the most "popular" language.
    protected function getLanguage($course)
    {
        $articlesToCheckLanguageIn = [];
        if (!empty($course->subtopics)) {
            foreach ($course->subtopics ?? [] as $topic) {
                $articlesToCheckLanguageIn = array_merge($articlesToCheckLanguageIn, $this->getTopicContentIds($topic, $articlesToCheckLanguageIn));
            }
        } else {
            $articlesToCheckLanguageIn = $this->getTopicContentIds($course, $articlesToCheckLanguageIn);
        }

        $articlesToCheckLanguageIn = array_unique($articlesToCheckLanguageIn);
        $languages = ['nb' => 0, 'nn' => 0, 'en' => 0, 'sv' => 0, 'sma' => 0];

        NdlaIdMapper::select('language_code')
            ->whereIn('ndla_id', $articlesToCheckLanguageIn)
            ->where('type', 'article')
            ->each(function ($article) use (&$languages) {
                if ($article->language_code) {
                    $languages[$article->language_code] = $languages[$article->language_code] + 1;
                }
            });

        $language = array_search(max($languages), $languages);

        if (!is_string($language)) {
            return 'nb';
        }

        if ($language === 'sma') {
            return 'sm'; // PS! This gets converted back to sma on the EdStep side.
        }

        return $language;
    }

    /**
     * @param $topic
     * @param array $articlesToCheckLanguageIn
     * @return array
     */
    protected function getTopicContentIds($topic, array $articlesToCheckLanguageIn): array
    {
        foreach ($topic->coreResources ?? [] as $resource) {
            if ($resource->contentUri ?? null) {
                $articlesToCheckLanguageIn[] = last(explode(':', $resource->contentUri));
            }
        }
        foreach ($topic->supplementaryResources ?? [] as $resource) {
            if ($resource->contentUri ?? null) {
                $articlesToCheckLanguageIn[] = last(explode(':', $resource->contentUri));
            }
        }
        return $articlesToCheckLanguageIn;
    }
}
