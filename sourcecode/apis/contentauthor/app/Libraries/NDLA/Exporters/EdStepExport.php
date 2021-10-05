<?php

namespace App\Libraries\NDLA\Exporters;


use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use App\Libraries\NDLA\API\GraphQLApi;
use App\Traits\HandlesHugeSubjectMemoryLimit;
use App\Libraries\NDLA\Exporters\Transformers\SubjectTransformer;
use App\Libraries\NDLA\Exporters\Serializers\EdStepExportSerializer;

class EdStepExport
{
    use HandlesHugeSubjectMemoryLimit;

    protected $fractal;

    public function __construct()
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new EdStepExportSerializer());
    }

    public function processSubject($subjectId)
    {
        $this->expandMemoryLimitForHugeSubjects($subjectId);

        /** @var GraphQLApi $subject */
        $gql = resolve(GraphQLApi::class);
        $subject = $gql->fetchSubject($subjectId);

        $subjectItem = new Item($subject, new SubjectTransformer);
        $subjectData = $this->fractal->createData($subjectItem)->toArray();

        $subjectData = json_encode($subjectData);
        $subjectData = json_decode($subjectData);
        // Clean up, No idea why a single object in a collection is "promoted" to an object
        foreach ($subjectData->courses as $course) {
            if (is_object($course->children)) {
                $course->children = [$course->children];
            }
            foreach ($course->children as $module) {
                if (is_object($module->resources)) {
                    $module->resources = [$module->resources];
                }
            }
        }

        return $subjectData;
    }
}
