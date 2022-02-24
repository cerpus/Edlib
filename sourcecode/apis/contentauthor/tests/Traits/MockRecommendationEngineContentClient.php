<?php

namespace Tests\Traits;

use Cerpus\REContentClient\ContentClient;
use Closure;

trait MockRecommendationEngineContentClient
{
    public function mockContentClient(array $methods): void
    {
        $contentClient = $this->createPartialMock(ContentClient::class, array_keys($methods));

        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof Closure) {
                $contentClient->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $contentClient->method($method)->willReturn($returnValue);
        }

        app()->instance(ContentClient::class, $contentClient);
    }
}
