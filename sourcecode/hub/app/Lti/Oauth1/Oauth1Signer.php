<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use App\Lti\Oauth1\Oauth1Claims as Claim;
use Psr\Clock\ClockInterface;
use Random\Randomizer;

use function base64_encode;
use function hash_hmac;
use function rawurlencode;

final readonly class Oauth1Signer implements Oauth1SignerInterface
{
    public function __construct(
        private ClockInterface $clock,
        private Randomizer $randomizer,
    ) {
    }

    public function calculateSignature(
        Oauth1Request $request,
        Oauth1Credentials $clientCredentials,
        Oauth1Credentials|null $tokenCredentials = null,
    ): string {
        $request = $request->without(Claim::SIGNATURE);

        return base64_encode(hash_hmac(
            'sha1',
            $request->generateSignatureBaseString(),
            sprintf(
                '%s&%s',
                rawurlencode($clientCredentials->secret),
                rawurlencode($tokenCredentials?->secret ?? ''),
            ),
            binary: true,
        ));
    }

    public function getSignatureMethod(Oauth1Request $request): string
    {
        return 'HMAC-SHA1';
    }

    public function sign(
        Oauth1Request $request,
        Oauth1Credentials $clientCredentials,
        Oauth1Credentials|null $tokenCredentials = null,
    ): Oauth1Request {
        $timestamp = $this->clock->now()->getTimestamp();
        $nonce = base64_encode($this->randomizer->getBytes(24));

        $request = $request
            ->without(Claim::SIGNATURE)
            ->with(Claim::SIGNATURE_METHOD, 'HMAC-SHA1')
            ->with(Claim::CONSUMER_KEY, $clientCredentials->key)
            ->with(Claim::NONCE, $nonce)
            ->with(Claim::TIMESTAMP, (string) $timestamp)
            ->with(Claim::VERSION, '1.0');

        if ($tokenCredentials) {
            $request = $request->with(Claim::TOKEN, $tokenCredentials->key);
        }

        $signature = $this->calculateSignature(
            $request,
            $clientCredentials,
            $tokenCredentials,
        );

        return $request->with(Claim::SIGNATURE, $signature);
    }
}
