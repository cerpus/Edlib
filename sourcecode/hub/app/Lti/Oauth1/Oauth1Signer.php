<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use App\Lti\Oauth1\Oauth1Claims as Claim;
use Psr\Clock\ClockInterface;
use Random\Randomizer;

use function base64_encode;
use function hash_hmac;
use function rawurlencode;

final readonly class Oauth1Signer
{
    public function __construct(
        private ClockInterface $clock,
        private Randomizer $randomizer,
    ) {
    }

    public function sign(
        Oauth1Request $request,
        Oauth1Credentials $credentials,
    ): Oauth1Request {
        if (!$request->has(Claim::TIMESTAMP)) {
            $timestamp = $this->clock->now()->getTimestamp();
            $request = $request->with(Claim::TIMESTAMP, (string) $timestamp);
        }

        if (!$request->has(Claim::NONCE)) {
            $nonce = base64_encode($this->randomizer->getBytes(24));
            $request = $request->with(Claim::NONCE, $nonce);
        }

        $request = $request
            ->without(Claim::SIGNATURE)
            ->with(Claim::CONSUMER_KEY, $credentials->consumerKey)
            ->with(Claim::SIGNATURE_METHOD, 'HMAC-SHA1')
            ->with(Claim::VERSION, '1.0');

        return $request->with(Claim::SIGNATURE, base64_encode(hash_hmac(
            'sha1',
            $request->generateSignatureBaseString(),
            rawurlencode($credentials->consumerSecret) . '&',
            binary: true,
        )));
    }
}
