<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testOnlyAdminsCanShowUsers(): void
    {
        $user = User::factory()->create();

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->getJson('/api/users/' . $user->id)
            ->assertForbidden();
    }

    public function testShowsUser(): void
    {
        $user = User::factory()->admin()->create();

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->getJson('/api/users/' . $user->id)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'locale' => $user->locale,
                    'theme' => $user->theme,
                    'created_at' => $user->created_at?->format('c'),
                    'updated_at' => $user->updated_at?->format('c'),
                    'admin' => true,
                ],
            ]);
    }

    public function testShowsUserByEmail(): void
    {
        $user = User::factory()->admin()->create();

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->getJson('/api/users/by-email/' . rawurlencode($user->email))
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'locale' => $user->locale,
                    'theme' => $user->theme,
                    'created_at' => $user->created_at?->format('c'),
                    'updated_at' => $user->updated_at?->format('c'),
                    'admin' => true,
                ],
            ]);
    }

    public function testOnlyAdminsCanCreateUsers(): void
    {
        $user = User::factory()->create();

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->postJson('/api/users', [
                'name' => 'Jason A. Pi',
                'email' => 'jason@edlib.test',
            ])
            ->assertForbidden();
    }

    public function testCreatesUser(): void
    {
        $user = User::factory()->admin()->create();

        $this->freezeTime();

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->postJson('/api/users', [
                'name' => 'Jason A. Pi',
                'email' => 'jason@edlib.test',
            ])
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->has('id')
                            ->where('name', 'Jason A. Pi')
                            ->where('email', 'jason@edlib.test')
                            ->where('theme', null)
                            ->where('locale', 'en')
                            ->where('debug_mode', false)
                            ->where('created_at', Carbon::now()->format('c'))
                            ->where('updated_at', Carbon::now()->format('c'))
                            ->where('admin', false),
                    ),
            );
    }

    public function testCreatedAtIsOverrideable(): void
    {
        $user = User::factory()->admin()->create();

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->postJson('/api/users', [
                'name' => 'Tim E. Traveller',
                'email' => 'tim@edlib.test',
                'created_at' => '2000-01-01T00:00:00Z',
            ])
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->where('created_at', '2000-01-01T00:00:00+00:00')
                            ->etc(),
                    ),
            );
    }

    public function testEmailIsNormalizedBeforeSaving(): void
    {
        $user = User::factory()->admin()->create();

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->postJson('/api/users', [
                'name' => 'E. Mel',
                'email' => 'E.MEL@EDLIB.TEST',
            ])
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->where('email', 'e.mel@edlib.test')
                            ->etc(),
                    ),
            );
    }
}
