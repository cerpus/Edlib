<?php

namespace Tests\Feature;

use App\Link;
use App\Game;
use App\Article;
use App\H5PContent;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\MockMetadataService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentTagControllerTest extends TestCase
{
    use RefreshDatabase, MockMetadataService;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupMetadataService([
            'setEntityType' => null,
            'setEntityId' => null,
            'getData' => [],
        ]);
    }

    public function testH5PEndpointExist()
    {
        $h5p = factory(H5PContent::class)->create();

        $response = $this->get(route('h5p.tags', $h5p->id));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'application/json');

        $response = $this->get(route('h5p.tags', 'not-an-id'));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testArticleEndpointExist()
    {
        $article = factory(Article::class)->create();

        $response = $this->get(route('article.tags', $article->id));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'application/json');

        $response = $this->get(route('article.tags', 'not-an-id'));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGameEndpointExist()
    {
        $game = factory(Game::class)->create();

        $response = $this->get(route('game.tags', $game->id));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'application/json');

        $response = $this->get(route('game.tags', 'not-an-id'));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testLinkEndpointExist()
    {
        $link = factory(Link::class)->create();

        $response = $this->get(route('link.tags', $link->id));
        $response->assertStatus(Response::HTTP_OK);
        $response->assertHeader('Content-Type', 'application/json');

        $response = $this->get(route('link.tags', 'not-an-id'));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
