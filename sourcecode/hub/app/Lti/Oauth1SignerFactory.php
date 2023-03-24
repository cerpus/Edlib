<?php

namespace App\Lti;

use Psr\Clock\ClockInterface;
use Random\Randomizer;

readonly class Oauth1SignerFactory
{
    public function __construct(
        private ClockInterface $clock,
        private Randomizer $randomizer = new Randomizer(),
    ) {
    }

    public function create(Oauth1Credentials $credentials): Oauth1Signer
    {
        return new Oauth1Signer($credentials, $this->randomizer, $this->clock);
    }
}
