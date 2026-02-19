<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Oauth1;

use App\EdlibResourceKit\Oauth1\Claim as Claim;
use Psr\Clock\ClockInterface;
use Random\Randomizer;

use function base64_encode;
use function hash_hmac;
use function rawurlencode;

final readonly class Signer implements SignerInterface
{
    public function __construct(
        private ClockInterface $clock,
        private Randomizer $randomizer,
    ) {
    }

    public function calculateSignature(
        Request $request,
        Credentials $clientCredentials,
        Credentials|null $tokenCredentials = null,
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

    public function getSignatureMethod(Request $request): string
    {
        return 'HMAC-SHA1';
    }

    public function sign(
        Request $request,
        Credentials $clientCredentials,
        Credentials|null $tokenCredentials = null,
    ): Request {
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
