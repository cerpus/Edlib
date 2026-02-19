<?php

namespace App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\ScoreConstraints;

interface ScoreConstraintsMapperInterface
{
    public function map(array $data): ScoreConstraints|null;
}
