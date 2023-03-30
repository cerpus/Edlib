<?php

declare(strict_types=1);

namespace Tests\Unit\Lti\Oauth1;

use App\Lti\Oauth1\Oauth1Credentials;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Oauth1Credentials::class)]
final class Oauth1CredentialsTest extends TestCase
{
    public function testCannotUseEmptyConsumerKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Oauth1Credentials('', 'not empty');
    }

    public function testCannotUseEmptyConsumerSecret(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Oauth1Credentials('not empty', '');
    }
}
