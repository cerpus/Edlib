<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Models\Content;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

use function parse_str;
use function parse_url;
use function url;

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
            ->post('https://hub-test-ndla-legacy.edlib.test/select?locale=nb-no&canReturnResources=true')
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('url')
                    ->where('url', function (string $url) {
                        $this->assertStringStartsWith('https://hub-test-ndla-legacy.edlib.test/select?', $url);

                        $qs = parse_url($url, PHP_URL_QUERY);
                        $this->assertIsString($qs);
                        parse_str($qs, $query);

                        $this->assertArrayHasKey('deep_link', $query);
                        $this->assertArrayHasKey('user', $query);

                        $this->assertArrayHasKey('admin', $query);
                        $this->assertSame('0', $query['admin']);

                        $this->assertArrayHasKey('locale', $query);
                        $this->assertSame('nb_no', $query['locale']);

                        $this->assertArrayHasKey('user', $query);
                        $this->assertIsString($query['user']);
                        $encrypter = $this->app->make(Encrypter::class);
                        $user = $encrypter->decrypt($query['user']);

                        $this->assertEquals([
                            'name' => 'Bob',
                            'email' => 'bob@example.com',
                        ], $user);

                        return true;
                    }),
            );
    }

    public function testLaunchesSelectByUrl(): void
    {
        $jwt = Jwt::sign([
            'https://ndla.no/user_name' => 'Bob',
            'https://ndla.no/user_email' => 'bob@example.com',
            'https://ndla.no/ndla_id' => '89w7tg87as8g78a7s8',
            'exp' => time() + 600,
            'scope' => 'openid profile email',
        ]);

        $content = Content::factory()
            ->edlib2UsageId('dd72be4f-672d-44b4-bc0c-570947b17437')
            ->withPublishedVersion()
            ->create();

        $this
            ->withToken($jwt)
            ->post('https://hub-test-ndla-legacy.edlib.test/select/edit/byurl', [
                'url' => 'https://hub-test-ndla-legacy.edlib.test/resource/dd72be4f-672d-44b4-bc0c-570947b17437?locale=nb-no',
            ])
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('url')
                    ->where('url', function (string $url) use ($content) {
                        $this->assertStringStartsWith('https://hub-test-ndla-legacy.edlib.test/select?', $url);

                        $qs = parse_url($url, PHP_URL_QUERY);
                        $this->assertIsString($qs);
                        parse_str($qs, $query);

                        $this->assertArrayHasKey('content_id', $query);
                        $this->assertSame($content->id, $query['content_id']);

                        return true;
                    }),
            );
    }

    public function testRendersSelectIframe(): void
    {
        $url = url()->signedRoute('ndla-legacy.select-iframe', [
            'user' => $this->app->make(Encrypter::class)->encrypt([
                'name' => 'Ender Ella',
                'email' => 'ender@example.com',
            ]),
            'deep_link' => true,
            'admin' => false,
        ]);

        $this->get($url)
            ->assertOk()
            // TODO: add more useful assertions
            ->assertSeeHtml('<form');
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
