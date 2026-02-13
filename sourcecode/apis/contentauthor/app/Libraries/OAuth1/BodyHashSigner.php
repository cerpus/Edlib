<?php

declare(strict_types=1);

namespace App\Libraries\OAuth1;

use Cerpus\EdlibResourceKit\Oauth1\Claim as Claim;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Psr\Clock\ClockInterface;
use Random\Randomizer;

use function base64_encode;
use function hash_hmac;
use function rawurlencode;

final readonly class BodyHashSigner
{
    public function __construct(
        private ClockInterface $clock,
        private Randomizer $randomizer,
    ) {
    }

    public function calculateSignature(
        BodyHashRequest $request,
        Credentials $clientCredentials,
        Credentials|null $tokenCredentials = null,
    ): string {
        $request = $request->removeOAuthParameter(Claim::SIGNATURE)
            ->removeOAuthParameter('realm');

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

    public function getSignatureMethod(): string
    {
        return 'HMAC-SHA1';
    }

    public function sign(
        BodyHashRequest $request,
        Credentials $clientCredentials,
        Credentials|null $tokenCredentials = null,
    ): BodyHashRequest {
        $timestamp = $this->clock->now()->getTimestamp();
        $nonce = base64_encode($this->randomizer->getBytes(24));

        $request = $request
            ->removeOAuthParameter(Claim::SIGNATURE)
            ->removeOAuthParameter('realm')
            ->setOAuthParameter(Claim::SIGNATURE_METHOD, 'HMAC-SHA1')
            ->setOAuthParameter(Claim::CONSUMER_KEY, $clientCredentials->key)
            ->setOAuthParameter(Claim::NONCE, $nonce)
            ->setOAuthParameter(Claim::TIMESTAMP, (string) $timestamp)
            ->setOAuthParameter(Claim::VERSION, '1.0');

        if ($tokenCredentials) {
            $request = $request->setOAuthParameter(Claim::TOKEN, $tokenCredentials->key);
        }

        $signature = $this->calculateSignature(
            $request,
            $clientCredentials,
            $tokenCredentials,
        );

        return $request->setOAuthParameter(Claim::SIGNATURE, $signature);
    }
}
