<?php

namespace App\Lti;

use Psr\Clock\ClockInterface;
use Random\Randomizer;

use function array_keys;
use function array_map;
use function base64_encode;
use function hash_hmac;
use function implode;
use function ksort;
use function rawurlencode;
use function strtoupper;

final readonly class Oauth1Signer
{
    public function __construct(
        private Oauth1Credentials $credentials,
        private Randomizer $randomizer,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @param string[] $parameters
     */
    public function sign(
        string $method,
        string $url,
        array $parameters = [],
    ): Oauth1Request {
        $request = new Oauth1Request(
            consumerKey: $this->credentials->consumerKey,
            nonce: base64_encode($this->randomizer->getBytes(24)),
            signatureMethod: 'HMAC-SHA1',
            timestamp: $this->clock->now()->getTimestamp(),
            parameters: $parameters,
        );

        $signature = base64_encode(hash_hmac(
            'sha1',
            $this->generateSignatureBaseString($method, $url, [
                ...$parameters,
                ...$request->toArray(),
            ]),
            rawurlencode($this->credentials->consumerSecret) . '&',
            binary: true,
        ));

        return $request->withSignature($signature);
    }

    /**
     * @param string[] $parameters
     */
    private function generateSignatureBaseString(
        string $method,
        string $url,
        array $parameters = [],
    ): string {
        // https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.1.3.2
        $parameters = array_combine(
            array_map(rawurlencode(...), array_keys($parameters)),
            array_map(rawurlencode(...), $parameters),
        );
        ksort($parameters);

        return strtoupper($method) .
            '&' . rawurlencode($url) .
            '&' . rawurlencode(implode('&', array_map(
                fn (string $value, string $key): string => "$key=$value",
                $parameters,
                array_keys($parameters),
            )));
    }
}
