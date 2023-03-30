<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use App\Lti\Oauth1\Oauth1Claims as Claim;
use function hash_equals;

final readonly class Oauth1Validator implements Oauth1ValidatorInterface
{
    public function __construct(private Oauth1Signer $signer)
    {
    }

    public function validate(Oauth1Request $request, Oauth1Credentials $credentials): bool
    {
        if (
            !$request->has(Claim::CONSUMER_KEY) ||
            $request->get(Claim::CONSUMER_KEY) !== $credentials->consumerKey ||
            !$request->has(Claim::SIGNATURE) ||
            !$request->has(Claim::VERSION) || $request->get(Claim::VERSION) !== '1.0'
        ) {
            return false;
        }

        $signed = $this->signer->sign($request, $credentials);

        return $signed->get(Claim::SIGNATURE_METHOD) === $request->get(Claim::SIGNATURE_METHOD) &&
            hash_equals($signed->get(Claim::SIGNATURE), $request->get(Claim::SIGNATURE));
    }
}
