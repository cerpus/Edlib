<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\ApiModels\User;
use App\Apis\AuthApiService;
use App\Article;
use App\ContentVersion;
use App\H5PContent;
use App\NdlaIdMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_getOwnerData(): void
    {
        $authMock = $this->createMock(AuthApiService::class);
        $this->instance(AuthApiService::class, $authMock);

        $userId = $this->faker->uuid;
        $user = new User($userId, 'Emily', 'QuackFaster', 'eq@duckburg.quack');

        $authMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $content = H5PContent::factory()->create([
            'user_id' => $userId,
        ]);

        $owner = $content->getOwnerData();

        $this->assertSame($userId, $owner->id);
        $this->assertSame('Emily', $owner->firstname);
        $this->assertSame('QuackFaster', $owner->lastname);
        $this->assertSame('eq@duckburg.quack', $owner->email);
    }

    public function test_isImported(): void
    {
        $parentArticle = Article::factory()->create();
        $article = Article::factory()->create([
            'parent_id' => $this->faker->uuid,
        ]);

        $parentVersion = ContentVersion::factory()->create([
            'id' => $parentArticle->version_id,
            'content_id' => $parentArticle->id,
        ]);
        $version = ContentVersion::factory()->create([
            'id' => $article->version_id,
            'content_id' => $article->id,
            'parent_id' => $parentVersion->id,
        ]);

        NdlaIdMapper::create([
            'ndla_id' => $this->faker->uuid,
            'ca_id' => $parentArticle->id,
            'type' => 'testing',
        ]);

        $isImported = $article->isImported();
        $this->assertTrue($isImported);
    }
}
