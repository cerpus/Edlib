<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Message\DeepLinking;

class ScoreConstraints
{
    public function __construct(
        private readonly float|null $normalMaximum = null,
        private readonly float|null $extraCreditMaximum = null,
    ) {
    }

    public function getNormalMaximum(): float|null
    {
        return $this->normalMaximum;
    }

    public function getExtraCreditMaximum(): float|null
    {
        return $this->extraCreditMaximum;
    }

    public function getTotalMaximum(): float
    {
        return ($this->normalMaximum ?? 0) + ($this->extraCreditMaximum ?? 0);
    }
}
