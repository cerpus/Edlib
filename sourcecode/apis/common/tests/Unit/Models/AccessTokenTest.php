<?php

namespace Tests\Unit\Models;

use App\Models\AccessToken;
use Tests\TestCase;
use function strlen;

class AccessTokenTest extends TestCase
{
    public function testTokenHasExpectedLength(): void
    {
        $accessToken = AccessToken::make();

        $this->assertEquals(48, strlen($accessToken->token));
    }

    public function testNoTwoTokensAreTheSame(): void
    {
        $accessToken1 = AccessToken::make();
        $accessToken2 = AccessToken::make();

        $this->assertNotSame($accessToken2->token, $accessToken1->token);
    }
}
