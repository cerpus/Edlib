<?php

declare(strict_types=1);

namespace Feature\NdlaLegacy;

use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Feature\NdlaLegacy\Jwt;
use Tests\TestCase;

use function parse_str;
use function parse_url;

use const PHP_URL_QUERY;

final class SelectTest extends TestCase
{
    use RefreshDatabase;

    public function testLaunchesSelect(): void
    {
        $jwt = Jwt::sign([
            'https://ndla.no/user_name' => 'Bob',
            'https://ndla.no/user_email' => 'bob@example.com',
            'https://ndla.no/ndla_id' => '89w7tg87as8g78a7s8',
            'exp' => time() + 600,
            'scope' => 'openid profile email',
        ]);

        $this
            ->withToken($jwt)
            ->post('https://hub-test-ndla-legacy.edlib.test/select?locale=nb-no')
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has('url')
                    ->where('url', function (string $url) {
                        $this->assertStringStartsWith('https://hub-test-ndla-legacy.edlib.test/select?', $url);

                        $qs = parse_url($url, PHP_URL_QUERY);
                        $this->assertIsString($qs);
                        parse_str($qs, $query);

                        $this->assertArrayHasKey('deep_link', $query);
                        $this->assertArrayHasKey('user', $query);

                        $this->assertArrayHasKey('locale', $query);
                        $this->assertSame('nb_no', $query['locale']);

                        $this->assertArrayHasKey('user', $query);
                        $this->assertIsString($query['user']);
                        $encrypter = $this->app->make(Encrypter::class);
                        $user = $encrypter->decrypt($query['user']);

                        $this->assertEquals([
                            'name' => 'Bob',
                            'email' => 'bob@example.com',
                            'admin' => false,
                        ], $user);

                        return true;
                    })
            );
    }

    public function testSelectRequiresJwt(): void
    {
        $this
            ->post('http://hub-test-ndla-legacy.edlib.test/select')
            ->assertUnauthorized();
    }

    public function testSelectRequiresUnexpiredJwt(): void
    {
        $jwt = Jwt::sign([
            'https://ndla.no/user_name' => 'Bob',
            'https://ndla.no/user_email' => 'bob@example.com',
            'https://ndla.no/ndla_id' => '89w7tg87as8g78a7s8',
            'exp' => 1234567890, // way in the past
            'scope' => 'openid profile email',
        ]);

        $this
            ->withToken($jwt)
            ->post('http://hub-test-ndla-legacy.edlib.test/select')
            ->assertUnauthorized();
    }
}
