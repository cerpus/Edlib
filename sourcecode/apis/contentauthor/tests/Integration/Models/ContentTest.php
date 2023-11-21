<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\ApiModels\User;
use App\Apis\AuthApiService;
use App\H5PContent;
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
}
