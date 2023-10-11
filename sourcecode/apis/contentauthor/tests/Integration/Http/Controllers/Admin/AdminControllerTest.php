<?php

namespace Tests\Integration\Http\Controllers\Admin;

use App\ApiModels\User;
use App\Apis\AuthApiService;
use App\ContentLock;
use App\Events\ResourceSaved;
use App\H5PContent;
use App\H5PLibrary;
use App\Http\Controllers\Admin\AdminController;
use App\Libraries\H5P\H5PLibraryAdmin;
use Generator;
use H5PCore;
use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_viewMaxScoreOverview(): void
    {
        $core = $this->createMock(H5PCore::class);
        $this->instance(H5PCore::class, $core);
        $core->expects($this->once())->method('getLocalization')->willReturn([]);

        Storage::fake('test');
        Storage::put('libraries/H5P.Foobar-1.2/presave.js', 'the content');
        Storage::put('libraries/H5P.Foobar-1.42/presave.js', 'the content');
        Storage::put('libraries/H5P.Toolbar-1.2/presave.js', 'the content');

        $library1 = H5PLibrary::factory()->create();
        $library2 = H5PLibrary::factory()->create([
            'minor_version' => 42,
        ]);
        $library3 = H5PLibrary::factory()->create([
            'name' => 'H5P.Toolbar',
        ]);
        H5PContent::factory(1)->create([
            'library_id' => $library1->id,
            'max_score' => null,
        ]);
        H5PContent::factory(2)->create([
            'library_id' => $library2->id,
            'max_score' => null,
        ]);
        H5PContent::factory(3)->create([
            'library_id' => $library3->id,
            'max_score' => null,
        ]);
        H5PContent::factory()->create([
            'library_id' => $library3->id,
            'max_score' => 0,
            'bulk_calculated' => H5PLibraryAdmin::BULK_FAILED,
        ]);

        $controller = App()->make(AdminController::class);
        $view = $controller->viewMaxScoreOverview();
        $data = $view->getData();

        $this->assertArrayHasKey('libraries', $data);
        $this->assertArrayHasKey('scripts', $data);
        $this->assertArrayHasKey('scoreConfig', $data);
        $this->assertArrayHasKey('settings', $data);
        $this->assertArrayHasKey('numFailed', $data);
        $this->assertArrayHasKey('libraryPath', $data);

        $this->assertEquals(1, $data['numFailed']);
        $this->assertCount(3, $data['libraries']);
        $this->assertStringEndsWith('/content/assets/libraries', $data['libraryPath']);

        $libraries = $data['libraries'];
        $this->assertEquals(1, $libraries[0]->contents_count);
        $this->assertEquals(2, $libraries[1]->contents_count);
        $this->assertEquals(3, $libraries[2]->contents_count);

        foreach ($data['scripts'] as $script) {
            $this->assertStringNotContainsStringIgnoringCase('/js/presave/', $script);
            $this->assertStringNotContainsStringIgnoringCase('/presave.js', $script);
        }
    }

    public function test_updateMaxScore(): void
    {
        $library = H5PLibrary::factory()->create();
        $content = H5PContent::factory()->create([
            'library_id' => $library->id,
            'max_score' => null,
            'bulk_calculated' => H5PLibraryAdmin::BULK_UNTOUCHED,
        ]);

        $this->expectsEvents(ResourceSaved::class);
        $ret = $this->withSession(['user' => new GenericUser(['roles' => ['superadmin']])])
            ->post(route('admin.maxscore.update', [
                'libraries' => [$library->id],
                'scores' => json_encode([
                    $content->id => (object)['score' => 3, 'success' => true],
                ])
            ]))
            ->assertOk()
            ->decodeResponseJson();

        $this->assertEquals([], $ret['params']);
        $this->assertEquals(0, $ret['left']);
        $this->assertArrayHasKey('token', $ret);

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $content->id,
            'max_score' => 3,
            'bulk_calculated' => H5PLibraryAdmin::BULK_UPDATED,
        ]);
    }

    /** @dataProvider provider_index */
    public function test_index(int $lockCount): void
    {
        ContentLock::factory($lockCount)->create();
        $result = app(AdminController::class)->index();

        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertEquals($lockCount, $data['editLockCount']);
    }

    public function provider_index(): Generator
    {
        yield [0];
        yield [3];
    }

    public function test_viewFailedCalculations(): void
    {
        $authMock = $this->createMock(AuthApiService::class);
        $this->instance(AuthApiService::class, $authMock);

        $userId = $this->faker->uuid;
        $user = new User($userId, 'Emily', 'QuackFaster', 'eq@duckburg.quack');

        $authMock->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $library = H5PLibrary::factory()->create();

        H5PContent::factory()->create([
            'bulk_calculated' => H5PLibraryAdmin::BULK_UPDATED,
            'user_id' => $userId,
            'library_id' => $library->id,
        ]);
        H5PContent::factory()->create([
            'bulk_calculated' => H5PLibraryAdmin::BULK_UNTOUCHED,
            'user_id' => $userId,
            'library_id' => $library->id,
        ]);
        $failedResource = H5PContent::factory()->create([
            'bulk_calculated' => H5PLibraryAdmin::BULK_FAILED,
            'user_id' => $userId,
            'library_id' => $library->id,
        ]);

        $controller = app(AdminController::class);
        $result = $controller->viewFailedCalculations();
        $this->assertInstanceOf(View::class, $result);

        $data = $result->getData();
        $this->assertCount(1, $data['resources']);

        $resource = $data['resources']->first();
        $this->assertSame($failedResource->id, $resource->id);

        $this->assertStringContainsString($user->getEmail(), $resource->ownerName);
    }
}
