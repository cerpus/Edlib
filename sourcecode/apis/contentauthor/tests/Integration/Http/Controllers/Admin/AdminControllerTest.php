<?php

namespace Tests\Integration\Http\Controllers\Admin;

use App\Events\ResourceSaved;
use App\H5PContent;
use App\H5PLibrary;
use App\Http\Controllers\Admin\AdminController;
use App\Libraries\H5P\H5PLibraryAdmin;
use Illuminate\Auth\GenericUser;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_viewMaxScoreOverview(): void
    {
        $core = $this->createMock(\H5PCore::class);
        $this->instance(\H5PCore::class, $core);
        $core->expects($this->once())->method('getLocalization')->willReturn([]);

        $fsa = $this->createMock(FilesystemAdapter::class);
        $this->instance(FilesystemAdapter::class, $fsa);
        $fsa->expects($this->exactly(3))->method('exists')->willReturn(true);

        Storage::shouldReceive('disk')->andReturn($fsa);
        Storage::shouldReceive('getDefaultDriver')->andReturn('test');

        /** @var H5PLibrary $library1 */
        $library1 = H5PLibrary::factory()->create();
        /** @var H5PLibrary $library2 */
        $library2 = H5PLibrary::factory()->create([
            'minor_version' => 42,
        ]);
        /** @var H5PLibrary $library3 */
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
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create();
        /** @var H5PContent $content */
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
}
