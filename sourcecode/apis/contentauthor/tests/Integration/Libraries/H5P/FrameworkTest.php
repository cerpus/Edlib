<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\H5PLibrary;
use App\Libraries\H5P\Framework;
use Generator;
use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PDO;
use Tests\TestCase;

class FrameworkTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private Framework $framework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->framework = new Framework(
            $this->createMock(Client::class),
            $this->createMock(PDO::class),
            $this->createMock(Filesystem::class),
        );
    }

    /** @dataProvider provider_isPatchedLibrary */
    public function test_isPatchedLibrary(int $patchVersion, bool $expected)
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create();

        $this->assertSame($expected, $this->framework->isPatchedLibrary([
            'machineName' => $library->name,
            'majorVersion' => $library->major_version,
            'minorVersion' => $library->minor_version,
            'patchVersion' => $patchVersion,
        ]));
    }

    public function provider_isPatchedLibrary(): Generator
    {
        yield 'same patch' => [3, false];
        yield 'older patch' => [2, false];
        yield 'newer patch' => [4, true];
    }

    public function test_insertContent(): void
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create();
        $input = [
            'title' => 'Some title',
            'params' => '{"data":"empty"}',
            'embed_type' => 'div',
            'disable' => false,
            'slug' => 'slugger',
            'user_id' => $this->faker->uuid,
            'max_score' => 42,
            'is_published' => false,
            'is_private' => false,
            'is_draft' => false,
            'language_iso_639_3' => 'nob',
            'library' => [
                'libraryId' => $library->id,
            ],
            'metadata' => [
                'title' => 'Some title',
                'license' => 'CC BY-NC',
            ],
        ];

        $contentId = $this->framework->insertContent($input);

        $this->assertDatabaseHas('h5p_contents', ['id' => $contentId]);
        $this->assertDatabaseHas('h5p_contents_metadata', ['content_id' => $contentId]);

        $content = H5PContent::find($contentId);
        $this->assertSame($input['title'], $content->title);
        $this->assertSame($input['params'], $content->parameters);
        $this->assertSame($input['library']['libraryId'], $content->library->id);
        $this->assertSame($input['embed_type'], $content->embed_type);
        $this->assertSame($input['max_score'], $content->max_score);
        $this->assertSame($input['slug'], $content->slug);
        $this->assertSame($input['is_published'], $content->is_published);
        $this->assertSame($input['is_draft'], $content->is_draft);

        $this->assertSame($input['metadata']['license'], $content->metadata->license);
    }

    /** @dataProvider provider_isContentSlugAvailable */
    public function test_isContentSlugAvailable(string $slug, bool $expected): void
    {
        H5PContent::factory()->create([
            'slug' => 'taken',
        ]);

        $this->assertSame($expected, $this->framework->isContentSlugAvailable($slug));
    }

    public function provider_isContentSlugAvailable(): Generator
    {
        yield 'unavailable' => ['taken', false];
        yield 'available' => ['available', true];
    }

    public function test_getLibraryContentCount(): void
    {
        /** @var H5PLibrary $nr */
        $nr = H5PLibrary::factory()->create([
            'name' => 'H5P.NotRunnable',
            'runnable' => false,
        ]);
        H5PContent::factory(2)->create([
            'library_id' => $nr->id,
        ]);

        H5PLibrary::factory()->create([
            'name' => 'H5P.NoContent',
            'runnable' => true,
        ]);

        /** @var H5PLibrary $library */
        $library = H5PLibrary::factory()->create();
        H5PContent::factory(3)->create([
            'library_id' => $library->id,
        ]);

        $result = $this->framework->getLibraryContentCount();
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('H5P.Foobar 1.2', $result);
        $this->assertSame(3, $result['H5P.Foobar 1.2']);
    }
}
