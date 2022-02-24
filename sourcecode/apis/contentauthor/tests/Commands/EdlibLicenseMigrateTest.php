<?php

namespace Tests\Commands;

use App\Article;
use App\Events\ResourceSaved;
use App\Game;
use App\H5PContent;
use App\Http\Libraries\License;
use App\Link;
use App\QuestionSet;
use Cerpus\LicenseClient\Contracts\LicenseContract;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class EdlibLicenseMigrateTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider dataProvider */
    public function testHandle($className, string $tableName)
    {
        $this->expectsEvents([ResourceSaved::class]);
        /** @var MockObject|LicenseContract $license */
        $license = $this->createMock(LicenseContract::class);
        $license
            ->expects($this->exactly(12))
            ->method('getContent')
            ->willReturnOnConsecutiveCalls(
                (object)['licenses'=> []],
                (object)['licenses'=> [License::LICENSE_BY]],
                (object)['licenses'=> [License::LICENSE_BY_SA]],
                (object)['licenses'=> [License::LICENSE_BY_ND]],
                (object)['licenses'=> [License::LICENSE_BY_NC]],
                (object)['licenses'=> [License::LICENSE_BY_NC_SA]],
                (object)['licenses'=> [License::LICENSE_BY_NC_ND]],
                (object)['licenses'=> [License::LICENSE_CC0]],
                (object)['licenses'=> [License::LICENSE_PRIVATE]],
                (object)['licenses'=> [License::LICENSE_PDM]],
                (object)['licenses'=> [License::LICENSE_EDLIB]],
                false
            );
        app()->instance(LicenseContract::class, $license);

        /** @var Article|QuestionSet|Game|Link|H5PContent $class */
        $class = app($className);
        $class::factory()->count(12)->create();

        $this->assertDatabaseCount($tableName, 12);

        $this->artisan('edlib:license-migrate')
            ->expectsOutput('Migrating license data')
            ->assertSuccessful();

        $this->assertEquals(2, $class::where('license', '')->count());
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_BY]);
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_BY_SA]);
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_BY_ND]);
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_BY_NC]);
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_BY_NC_SA]);
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_BY_NC_ND]);
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_CC0]);
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_PDM]);
        $this->assertDatabaseHas($tableName, ['license' => License::LICENSE_EDLIB]);
        $this->assertEquals(2, $class::where('license', License::LICENSE_EDLIB)->count());

        $this->assertDatabaseMissing($tableName, ['license' => License::LICENSE_PRIVATE]);
    }

    public function dataProvider(): array
    {
        return [
            'Articles' => [Article::class, 'articles'],
            'Question sets' => [QuestionSet::class, 'question_sets'],
            'Games' => [Game::class, 'games'],
            'Links' => [Link::class, 'links'],
            'H5P contents' => [H5PContent::class, 'h5p_contents'],
        ];
    }

    public function testHandleException()
    {
        /** @var MockObject|LicenseContract $license */
        $license = $this->createMock(LicenseContract::class);
        $license
            ->expects($this->exactly(1))
            ->method('getContent')
            ->willThrowException(new Exception('Just testing', 42));
        app()->instance(LicenseContract::class, $license);

        /** @var Article $article */
        $article = Article::factory()->create();

        $this->assertDatabaseCount('articles', 1);

        $this->artisan('edlib:license-migrate')
            ->expectsOutput('Migrating license data')
            ->expectsOutput('Exception: (42) Just testing. Table: articles, id: ' . $article->id)
            ->assertSuccessful();

        $this->assertEquals(1, Article::where('license', '')->count());
    }
}
