<?php

namespace Tests\Integration\Http\Controllers\Admin;

use App\ApiModels\User;
use App\Apis\AuthApiService;
use App\Article;
use App\Http\Controllers\Admin\AdminArticleController;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\View\View;
use Tests\TestCase;

class AdminArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewFailedCalculations(): void
    {
        $faker = Factory::create();
        $authMock = $this->createMock(AuthApiService::class);
        $this->instance(AuthApiService::class, $authMock);

        $userId = $faker->uuid;
        $user = new User($userId, 'Emily', 'QuackFaster', 'eq@duckburg.quack');

        $authMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        Article::factory()->create([
            'bulk_calculated' => Article::BULK_UPDATED,
        ]);
        $failedResource = Article::factory()->create([
            'bulk_calculated' => Article::BULK_FAILED,
        ]);

        $result = app(AdminArticleController::class)->viewFailedCalculations();
        $this->assertInstanceOf(View::class, $result);

        $data = $result->getData();
        $this->assertCount(1, $data['resources']);

        $resource = $data['resources']->first();
        $this->assertSame($failedResource->id, $resource->id);

        $this->assertStringContainsString($user->getEmail(), $resource->ownerName);
    }
}
