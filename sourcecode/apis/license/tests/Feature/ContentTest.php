<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentLicense;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use DatabaseMigrations;

    public function testAddContent()
    {
        $this->withoutMiddleware();

        $this->assertCount(0, Content::all());
        $this->json('post', '/v1/content', [])
            ->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(0, Content::all());

        $this->json('post', '/v1/content', [
            'site' => 'testsite'
        ])->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(0, Content::all());

        $this->json('post', '/v1/content', [
            'site' => 'testsite',
            'content_id' => '1',
        ])->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(0, Content::all());

        $this->json('post', '/v1/content', [
            'site' => 'testsite',
            'content_id' => '1',
            'name' => 'Content 1',
        ])->seeJsonStructure([
            'id',
            'site',
            'content_id',
            'name',
            'licenses' => []
        ])
            ->seeJsonContains([
                'site' => 'testsite',
                'content_id' => '1',
                'name' => 'Content 1',
                'licenses' => []
            ]);

        $this->assertCount(1, Content::all());
    }

    public function testGetAllContent()
    {
        $this->withoutMiddleware();

        factory(Content::class, 2)->create();

        $this->assertCount(2, Content::all());

        $response = $this->json('get', '/v1/content')
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'site',
                    'content_id',
                    'name',
                    'licenses' => []
                ]
            ]);
        $result = $response->response->getData();
        $this->assertCount(2, $result);
    }

    public function testGetContentById()
    {
        $this->withoutMiddleware();

        factory(Content::class)->create(['content_id' => 'b52']);

        $response = $this->json('get', sprintf('/v1/content/%s', 1))
            ->seeJsonContains([
                'content_id' => 'b52'
            ]);
    }

    public function testDeleteContent()
    {
        $this->withoutMiddleware();

        $content = factory(Content::class)->create();
        $contentLicense = factory(ContentLicense::class)->create([
            'content_id' => $content->id
        ]);

        $this->assertCount(1, Content::all());
        $this->assertCount(1, Content::first()->licenses);
        $this->assertCount(1, ContentLicense::all());

        $this->json('delete', sprintf('/v1/content/%s', $content->id))
            ->assertResponseStatus(Response::HTTP_OK);

        $this->assertCount(0, Content::all());
        $this->assertCount(0, ContentLicense::all());
    }
}
