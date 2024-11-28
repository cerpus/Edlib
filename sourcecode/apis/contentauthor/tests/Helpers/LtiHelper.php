<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Tests\TestCase;

/**
 * @mixin TestCase
 */
trait LtiHelper
{
    /**
     * @return array<string, string>
     */
    private function getSignedLtiParams(string $url, array $params): array
    {
        $request = new Request('POST', $url, $params);
        $request = $this->app->make(SignerInterface::class)->sign(
            $request,
            $this->app->make(CredentialStoreInterface::class),
        );

        return $request->toArray();
    }
}
