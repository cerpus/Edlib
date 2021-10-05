<?php

namespace App\Traits;

trait HandlesHugeSubjectMemoryLimit
{
    public function expandMemoryLimitForHugeSubjects($subjectId)
    {
        $hugeSubjects = [
            'urn:subject:9', // Only History is known to cause Out of memory errors so far
        ];

        if (in_array($subjectId, $hugeSubjects) && function_exists('ini_set')) {
            ini_set('memory_limit', '1024M');
        }
    }
}
