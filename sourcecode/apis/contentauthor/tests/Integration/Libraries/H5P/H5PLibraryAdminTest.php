<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;
use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class H5PLibraryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_upgradeProgress_firstRequest(): void
    {
        $fromLib = H5PLibrary::factory()->create();
        $toLib = H5PLibrary::factory()->create(['major_version' => 2]);
        H5PContent::factory(2)->create([
            'library_id' => $fromLib->id,
        ]);

        $ret = $this->withSession(['user' => new GenericUser(['roles' => ['superadmin']])])
            ->post(route('admin.content-upgrade', ['id' => $fromLib->id]), [
                'libraryId' => $toLib->id,
            ])
            ->assertJson([
                'left' => 2,
                'skipped' => [],
                'params' => [],
            ])
            ->assertOk();

        $content = $ret->decodeResponseJson();
        $this->assertCount(2, $content['params']);
    }

    public function test_upgradeProgress_updateRequest(): void
    {
        $fromLib = H5PLibrary::factory()->create();
        $toLib = H5PLibrary::factory()->create(['major_version' => 2]);
        $libContent = H5PContent::factory(2)->create([
            'library_id' => $fromLib->id,
            'parameters' => 'old params',
            'filtered' => 'old filtered',
        ]);
        $skipped = [$libContent[1]->id];

        $ret = $this->withSession(['user' => new GenericUser(['roles' => ['superadmin']])])
            ->post(route('admin.content-upgrade', ['id' => $fromLib->id]), [
                'libraryId' => $toLib->id,
                'skipped' => json_encode($skipped),
                'params' => json_encode([
                    $libContent[0]->id => json_encode((object) [
                        'params' => 'new params',
                        'metadata' => (object)[
                            'title' => 'title',
                            'authors' => [
                                (object) [
                                    'name' => 'D.F. Duck',
                                    'role' => 'Tester',
                                ],
                            ],
                        ],
                    ]),
                ]),
            ])
            ->assertJson([
                'left' => 0,
                'skipped' => $skipped,
                'params' => [],
            ])
            ->assertOk();

        $content = $ret->decodeResponseJson();
        $this->assertCount(0, $content['params']);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $libContent[0]->id,
            'library_id' => $toLib->id,
            'parameters' => '"new params"',
            'filtered' => '',
        ]);
        $this->assertDatabaseHas('h5p_contents_metadata', [
            'content_id' => $libContent[0]->id,
            'authors' => '[{"name":"D.F. Duck","role":"Tester"}]',
        ]);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $libContent[1]->id,
            'library_id' => $fromLib->id,
        ]);
    }
}
