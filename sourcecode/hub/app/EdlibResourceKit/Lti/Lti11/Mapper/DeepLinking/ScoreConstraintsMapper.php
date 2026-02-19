<?php

namespace App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ScoreConstraints;

final readonly class ScoreConstraintsMapper implements ScoreConstraintsMapperInterface
{
    public function map(array $data): ScoreConstraints|null
    {
        return new ScoreConstraints(
            Prop::getNormalMaximum($data),
            Prop::getExtraCreditMaximum($data),
        );
    }
}
