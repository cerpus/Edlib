<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentLicense;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetLicense()
    {
        $this->withoutMiddleware();

        $mySiteContent = factory(Content::class)->create(['site' => 'mysite', 'content_id' => 1]);
        factory(ContentLicense::class)->create([
            'content_id' => $mySiteContent->id,
            'license_id' => 'BY-SA',
        ]);

        $this->json('GET', sprintf('/v1/site/%s/content/1', 'mysite'))
            ->seeJson([
                'licenses' => [
                    'BY-SA'
                ]
            ]);
    }

    public function testCreateContent()
    {
        $this->withoutMiddleware();

        $this->assertEquals(0, Content::all()->count());

        $this->json('post', sprintf('/v1/site/%s/content', 'a'), [
            'content_id' => 'x',
            'name' => 'Content x',
        ])->seeJsonEquals([
            'id' => 1,
            'content_id' => 'x',
            'licenses' => [],
            'name' => 'Content x',
            'site' => 'a',
        ]);

        $this->seeInDatabase('content', [
            'site' => 'a',
            'content_id' => 'x',
            'name' => 'Content x',
        ]);
    }

    public function testDeleteContentLicense()
    {
        $this->withoutMiddleware();

        $mySiteContent = factory(Content::class)->create(['site' => 'mysite', 'content_id' => 1]);
        factory(ContentLicense::class)->create([
            'content_id' => $mySiteContent->id,
            'license_id' => 'BY-SA',
        ]);
        $otherContent = factory(Content::class)->create(['site' => 'othersite', 'content_id' => 1]);
        factory(ContentLicense::class)->create([
            'content_id' => $otherContent->id,
        ]);

        $this->assertEquals(2, Content::all()->count());
        $this->assertEquals(2, ContentLicense::all()->count());

        $response = $this->json('delete', sprintf('/v1/site/%s/content/%s', 'mysite', '1'), ['license_id' => 'BY-SA']);
        $response->assertResponseStatus(Response::HTTP_OK);

        $this->assertEquals(2, Content::all()->count());
        $this->assertEquals(1, ContentLicense::all()->count());

        $this->seeInDatabase('content', ['site' => 'othersite', 'content_id' => 1]);
        $this->seeInDatabase('content_license', ['content_id' => $otherContent->id]);

        $this->seeInDatabase('content', ['site' => 'mysite', 'content_id' => 1]);
        $this->notSeeInDatabase('content_license', ['content_id' => $mySiteContent->id]);
    }
}
