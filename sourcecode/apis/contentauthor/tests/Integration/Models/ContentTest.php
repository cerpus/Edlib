<?php

namespace Tests\Integration\Models;

use App\ApiModels\User;
use App\Apis\AuthApiService;
use App\H5PContent;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_getOwnerData(): void
    {
        $faker = Factory::create();
        $authMock = $this->createMock(AuthApiService::class);
        $this->instance(AuthApiService::class, $authMock);

        $userId = $faker->uuid;
        $user = new User($userId, 'Emily', 'QuackFaster', 'eq@duckburg.quack');

        $authMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        /** @var H5PContent $content */
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
