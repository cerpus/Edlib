<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\H5P\H5PLibraryAdmin;
use App\Libraries\H5P\Packages\QuestionSet;
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
            ->assertOk()
            ->assertJson([
                'left' => 2,
                'skipped' => [],
                'params' => [],
            ]);

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
                        'metadata' => (object) [
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
            ->assertOk()
            ->assertJson([
                'left' => 0,
                'skipped' => $skipped,
                'params' => [],
            ]);

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

    public function test_upgradeMaxscore(): void
    {
        $libQs = H5PLibrary::factory()->create([
            'name' => QuestionSet::$machineName,
        ]);
        $contentQs = H5PContent::factory(2)->create([
            'library_id' => $libQs->id,
            'max_score' => 0,
            'bulk_calculated' => H5PLibraryAdmin::BULK_UNTOUCHED,
        ]);
        $libFoobar = H5PLibrary::factory()->create();
        $contentFoobar = H5PContent::factory(3)->create([
            'library_id' => $libFoobar->id,
            'max_score' => null,
            'bulk_calculated' => H5PLibraryAdmin::BULK_UNTOUCHED,
        ]);

        $libAdmin = app()->make(H5PLibraryAdmin::class);
        $ret = $libAdmin->upgradeMaxscore(
            [$libFoobar->id, $libQs->id],
            json_encode([
                $contentFoobar[0]->id => (object) ['score' => 2, 'success' => true],
                $contentFoobar[1]->id => (object) ['score' => 0, 'success' => false],
                $contentQs[0]->id => (object) ['score' => 3, 'success' => true],
            ]),
        );

        $this->assertEquals(2, $ret->left);
        $this->assertCount(2, $ret->params);

        $this->assertEquals($contentQs[1]->id, $ret->params[0]['id']);
        $this->assertEquals($contentFoobar[2]->id, $ret->params[1]['id']);

        $this->assertDatabaseHas('h5p_contents', [
            'id' => $contentFoobar[0]->id,
            'max_score' => 2,
            'bulk_calculated' => H5PLibraryAdmin::BULK_UPDATED,
        ]);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $contentFoobar[1]->id,
            'max_score' => 0,
            'bulk_calculated' => H5PLibraryAdmin::BULK_FAILED,
        ]);
        $this->assertDatabaseHas('h5p_contents', [
            'id' => $contentQs[0]->id,
            'max_score' => 3,
            'bulk_calculated' => H5PLibraryAdmin::BULK_UPDATED,
        ]);
    }
}
