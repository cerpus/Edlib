<?php

namespace App\ApiModels;

class LtiUser
{
    public function __construct(
        public string  $registrationId,
        public string  $deploymentId,
        public string  $externalId,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
    )
    {
    }
}
