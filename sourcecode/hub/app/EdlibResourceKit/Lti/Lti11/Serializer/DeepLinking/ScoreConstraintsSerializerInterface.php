<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\ScoreConstraints;

interface ScoreConstraintsSerializerInterface
{
    public function serialize(ScoreConstraints $scoreConstraints): array;
}
