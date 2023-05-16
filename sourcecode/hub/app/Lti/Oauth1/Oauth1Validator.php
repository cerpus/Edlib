<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use App\Lti\Exception\Oauth1ValidationException;
use App\Lti\Oauth1\Oauth1Claims as Claim;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;

use function hash_equals;

final readonly class Oauth1Validator implements Oauth1ValidatorInterface
{
    /**
     * @param positive-int $leewaySeconds
     *     The number of seconds +/- the timestamp provided in the request is
     *     allowed to deviate from the server time. Too little leeway will
     *     prevent clients with clock drift from sending valid requests, but too
     *     much leeway means increased vulnerability to resource exhaustion
     *     attacks, as used nonces must be kept track of for that amount of
     *     time.
     */
    public function __construct(
        private Oauth1CredentialStoreInterface $credentialStore,
        private Oauth1SignerInterface $signer,
        private CacheInterface $cache,
        private ClockInterface $clock,
        private int $leewaySeconds = 300,
    ) {
    }

    public function validate(Oauth1Request $request): void
    {
        if (!$request->has(Claim::CONSUMER_KEY)) {
            $this->error('No consumer key provided');
        }

        $credentials = $this->credentialStore
            ->findByKey($request->get(Claim::CONSUMER_KEY));

        if (!$credentials) {
            $this->error('Provided key does not correspond to any known consumer');
        }

        if (!$request->has(Claim::NONCE)) {
            $this->error('No nonce provided');
        }

        if (!$request->has(Claim::SIGNATURE)) {
            $this->error('No signature provided');
        }

        $expectedSignatureMethod = $this->signer->getSignatureMethod($request);

        if (
            !$request->has(Claim::SIGNATURE_METHOD) ||
            $request->get(Claim::SIGNATURE_METHOD) !== $expectedSignatureMethod
        ) {
            $this->error("Signature method must be \"$expectedSignatureMethod\"");
        }

        if (!$request->has(Claim::TIMESTAMP)) {
            $this->error('No timestamp provided');
        }

        if ($request->has(Claim::VERSION) && $request->get(Claim::VERSION) !== '1.0') {
            $this->error('Provided version must be "1.0" or omitted');
        }

        $now = $this->clock->now()->getTimestamp();
        $timestamp = (int) $request->get(Claim::TIMESTAMP);

        if (
            $timestamp < $now - $this->leewaySeconds ||
            $timestamp > $now + $this->leewaySeconds
        ) {
            $this->error('Provided time deviates too much from server time');
        }

        $signature = $this->signer->calculateSignature($request, $credentials);

        if (!hash_equals($signature, $request->get(Claim::SIGNATURE))) {
            $this->error('Provided signature does not match');
        }

        // The nonce is checked after signature validation to prevent
        // unauthorised requests touching the cache.
        $cacheKey = $this->generateNonceCacheKey($request);

        if ($this->cache->has($cacheKey)) {
            $this->error('Provided nonce has already been used');
        }

        $this->cache->set($cacheKey, true, $this->leewaySeconds);
    }

    /**
     * @throws Oauth1ValidationException
     */
    private function error(string $message): never
    {
        throw new Oauth1ValidationException($message);
    }

    private function generateNonceCacheKey(Oauth1Request $request): string
    {
        return 'oauth_nonce_' .
            hash(
                'xxh128',
                $request->get(Claim::CONSUMER_KEY) .
                $request->get(Claim::TIMESTAMP) .
                $request->get(Claim::NONCE),
            );
    }
}
