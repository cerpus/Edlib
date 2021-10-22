<?php

namespace App\Auth;

use App\Apis\AuthApiService;
use App\Exceptions\UnauthorizedException;
use App\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Psr\Log\LoggerInterface;

class IdentityServiceAuthenticator
{
    private Repository $cache;
    private AuthApiService $authApiService;
    private LoggerInterface $logger;

    public function __construct(
        Repository $cache,
        AuthApiService $authApiService,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->authApiService = $authApiService;
        $this->logger = $logger;
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \JsonException
     */
    private function retrieveFromInternalAuth(string $token): ?User
    {
        $serializer = new CompactSerializer();

        try {
            $kid = $serializer
                ->unserialize($token)
                ->getSignature(0)
                ->getProtectedHeaderParameter('kid');
        } catch (\InvalidArgumentException $e) {
            $this->logger->debug('Cannot retrieve KID from token');

            return null;
        }

        $cacheKey = 'jwks-keydata-'.$kid;
        $keyData = $this->cache->get($cacheKey);
        $doCache = false;

        if (!$keyData) {
            $doCache = true;
            $keyData = $this->authApiService->getJwks()->wait();
        }

        $jwks = JWKSet::createFromKeyData($keyData);

        if (!$jwks->has($kid)) {
            $this->logger->debug('JWKS does not contain KID from token');

            return null;
        }

        if ($doCache) {
            $this->cache->put($cacheKey, $keyData, 3600);
        }

        $jwsLoader = new JWSLoader(new JWSSerializerManager([
            $serializer,
        ]), new JWSVerifier(new AlgorithmManager([
            new RS256(),
        ])), new HeaderCheckerManager([
            new AlgorithmChecker(['RS256']),
        ], [
            new JWSTokenSupport(),
        ]));

        try {
            $jws = $jwsLoader->loadAndVerifyWithKeySet($token, $jwks, $signature);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());

            return null;
        }

        $payload = \json_decode($jws->getPayload(), true, 512, JSON_THROW_ON_ERROR);

        if (isset($payload['exp']) && time() > $payload['exp']) {
            $this->logger->debug('Key expired', [
                'exp' => $payload['exp'],
            ]);

            return null;
        }

        $userPayload = $payload["payload"]["user"];

        return new User([
            'id' => $userPayload["id"],
            'firstName' => $userPayload['firstName'],
            'lastName' => $userPayload['lastName'],
            'email' => $userPayload['email'],
            'isAdmin' => $userPayload['isAdmin'] == 1,
        ]);
    }

    /**
     * @throws \App\Exceptions\NotFoundException
     * @throws \JsonException
     */
    private function retrieveFromExternalAuth(Request $request, string $externalToken): ?User
    {
        try {
            $externalInfo = $this->authApiService->convertToken($externalToken);
            $request->merge([
                'internalToken' => $externalInfo['token']
            ]);

            return $externalInfo['user'];
        } catch (UnauthorizedException $e) {
            return null;
        }
    }

    /**
     * @throws \JsonException|\Psr\SimpleCache\InvalidArgumentException|\App\Exceptions\NotFoundException
     */
    public function __invoke(Request $request): ?User
    {
        $token = $request->bearerToken();

        if ($token === null) {
            $this->logger->debug('No bearer token in request');

            return null;
        }

        $userFromInternalAuth = $this->retrieveFromInternalAuth($token);

        if ($userFromInternalAuth) {
            $request->merge([
                'internalToken' => $token
            ]);
            return $userFromInternalAuth;
        }

        return $this->retrieveFromExternalAuth($request, $token);
    }
}
