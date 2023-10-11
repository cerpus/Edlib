<?php

namespace Tests\Integration\Models;

use App\Article;
use App\ContentAttribution;
use App\Libraries\DataObjects\Attribution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use TypeError;

class ContentAttributionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testModelWorks()
    {
        $article = Article::factory()->create();

        $attribution = $article->getAttribution();

        $this->assertInstanceOf(Attribution::class, $attribution);

        $this->assertObjectHasAttribute('origin', $attribution);
        $this->assertObjectHasAttribute('originators', $attribution);

        $this->assertNull($attribution->getOrigin());
        $this->assertEmpty($attribution->getOriginators());

        $newAttribution = new Attribution();
        $newAttribution->setOrigin('testing');
        $newAttribution->addOriginator('Test test', 'testrole');
        $newAttribution->addOriginator('Test test 2', 'testrole');

        $article->setAttribution($newAttribution);
        $article = $article->fresh();

        $this->assertEquals("testing", $article->getAttribution()->getOrigin());
        $this->assertCount(2, $article->getAttribution()->getOriginators());

        $this->assertDatabaseHas('content_attributions', ['content_id' => $article->id]);
        $this->assertCount(1, ContentAttribution::all());

        $newAttribution = new Attribution();
        $newAttribution->setOrigin('2testing');
        $newAttribution->addOriginator('2Test test', '2testrole');

        $article->setAttribution($newAttribution);
        $article = $article->fresh();

        $this->assertEquals("2testing", $article->getAttribution()->getOrigin());
        $this->assertCount(1, $article->getAttribution()->getOriginators());

        $this->assertDatabaseHas('content_attributions', ['content_id' => $article->id]);
        $this->assertCount(1, ContentAttribution::all());
    }

    public function testWillThrowExceptionIfSetAttributionIsNotAnAttributionObject()
    {
        $this->expectException(TypeError::class);
        $article = Article::factory()->create();

        /** @noinspection PhpParamsInspection */
        /** @phpstan-ignore-next-line  */
        $article->setAttribution("A string");
    }

    public function testRoleWillBeLowecasedAndFirstLetterUppercased()
    {
        $article = Article::factory()->create();

        $article->addAttributionOriginator('User 1', 'rOLE');

        $article = $article->fresh();

        $this->assertEquals('Role', $article->getAttribution()->getOriginators()[0]->role);
    }

    public function testYouCanGetAnAttributionTextForAPieceOfContent()
    {
        $article = Article::factory()->create();

        $this->assertEmpty($article->getAttributionAsString());

        $article->setAttributionOrigin('cerpus');
        $article->addAttributionOriginator('User 1', 'Author');
        $article->addAttributionOriginator('User 2', 'Editor');

        $this->assertEquals('Author: User 1. Editor: User 2. Originator: cerpus.', $article->fresh()->getAttributionAsString());
        $article->setAttributionOrigin('');
        $this->assertEquals('Author: User 1. Editor: User 2.', $article->fresh()->getAttributionAsString());
        $article->addAttributionOriginator('User 3', 'PhotographeR');
        $this->assertEquals('Author: User 1. Editor: User 2. Photographer: User 3.', $article->fresh()->getAttributionAsString());
    }

    public function testDecodingNonEmptyAndNonJsonWillNotFail()
    {
        $article = Article::factory()->create();

        // Bypass the eloquent mutators
        DB::table('content_attributions')
            ->insert([
                'content_id' => $article->id,
                'attribution' => 'A string',
            ]);

        $this->assertEmpty($article->getAttributionAsString());
    }
}
