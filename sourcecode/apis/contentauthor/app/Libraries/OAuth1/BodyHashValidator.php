<?php

declare(strict_types=1);

namespace App\Libraries\OAuth1;

use Cerpus\EdlibResourceKit\Oauth1\Claim as Claim;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

use function hash_equals;

final readonly class BodyHashValidator
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
        private BodyHashSigner $signer,
        private CacheInterface $cache,
        private ClockInterface $clock,
        private int $leewaySeconds = 300,
    ) {}

    /**
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function validate(
        BodyHashRequest $request,
        CredentialStoreInterface $credentialStore,
    ): void {
        if (!$request->isOAuth1()) {
            $this->error('Authorization header missing, or not OAuth');
        }

        if (!$request->hasOAuthParameter(Claim::CONSUMER_KEY)) {
            $this->error('No consumer key provided');
        }

        $credentials = $credentialStore
            ->findByKey($request->getOAuthParameter(Claim::CONSUMER_KEY));

        if (!$credentials) {
            $this->error('Provided key does not correspond to any known consumer');
        }

        if (!$request->hasOAuthParameter(Claim::NONCE)) {
            $this->error('No nonce provided');
        }

        if (!$request->hasOAuthParameter(Claim::SIGNATURE)) {
            $this->error('No signature provided');
        }

        if (!$request->hasOAuthParameter('oauth_body_hash')) {
            $this->error('No body hash provided');
        }

        $expectedSignatureMethod = $this->signer->getSignatureMethod();

        if (
            !$request->hasOAuthParameter(Claim::SIGNATURE_METHOD) ||
            $request->getOAuthParameter(Claim::SIGNATURE_METHOD) !== $expectedSignatureMethod
        ) {
            $this->error("Signature method must be \"$expectedSignatureMethod\"");
        }

        if (!$request->hasOAuthParameter(Claim::TIMESTAMP)) {
            $this->error('No timestamp provided');
        }

        if ($request->hasOAuthParameter(Claim::VERSION) && $request->getOAuthParameter(Claim::VERSION) !== '1.0') {
            $this->error('Provided version must be "1.0" or omitted');
        }

        $now = $this->clock->now()->getTimestamp();
        $timestamp = (int)$request->getOAuthParameter(Claim::TIMESTAMP);

        if (
            $timestamp < $now - $this->leewaySeconds ||
            $timestamp > $now + $this->leewaySeconds
        ) {
            $this->error('Provided time deviates too much from server time');
        }

        if ($request->getOAuthParameter('oauth_body_hash') !== $request->generateBodyHash()) {
            $this->error('Invalid body hash');
        }

        $signature = $this->signer->calculateSignature($request, $credentials);

        if (!hash_equals($signature, $request->getOAuthParameter(Claim::SIGNATURE))) {
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
     * @throws ValidationException
     */
    private function error(string $message): never
    {
        throw new ValidationException($message);
    }

    private function generateNonceCacheKey(BodyHashRequest $request): string
    {
        return 'oauth_nonce_' .
            hash(
                'xxh128',
                $request->getOAuthParameter(Claim::CONSUMER_KEY) .
                $request->getOAuthParameter(Claim::TIMESTAMP) .
                $request->getOAuthParameter(Claim::NONCE),
            );
    }
}
