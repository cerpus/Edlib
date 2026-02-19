<?php

namespace App\EdlibResourceKit\Lti\Message\DeepLinking;

class LineItem
{
    public function __construct(
        private readonly ScoreConstraints|null $scoreConstraints = null,
    ) {
    }

    public function getScoreConstraints(): ScoreConstraints|null
    {
        return $this->scoreConstraints;
    }
}
