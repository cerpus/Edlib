<?php
namespace Tests\Content;

use App\User;
use App\Article;
use App\H5PContent;
use Tests\TestCase;
use App\H5PCollaborator;
use App\ArticleCollaborator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function test_ArticleIsCollaborator()
    {
        $user = User::factory()->make();
        $article = Article::factory()->create(['owner_id' => $user->auth_id]);
        $collaborators =ArticleCollaborator::factory()->count(3)->make();
        $article->collaborators()->saveMany($collaborators);
        $article = $article->fresh();
        $this->assertCount(3, $article->collaborators);
        $theCollaborator = $collaborators->first();
        $this->withSession(['verifiedEmails' => [$theCollaborator->email]])
            ->assertTrue($article->isCollaborator($theCollaborator->email));
        $this->withSession(['verifiedEmails' => ['a@b.com']])
            ->assertFalse($article->isCollaborator());
    }

    public function test_H5PIsCollaborator()
    {
        $user = User::factory()->make();
        $h5p = H5PContent::factory()->create(['user_id' => $user->auth_id]);
        $collaborators = H5PCollaborator::factory()->count(3)->make();
        $h5p->collaborators()->saveMany($collaborators);
        $h5p = $h5p->fresh();
        $this->assertCount(3, $h5p->collaborators);
        $theCollaborator = $collaborators->first();
        $this->withSession(['verifiedEmails' => [$theCollaborator->email]])
            ->assertTrue($h5p->isCollaborator($theCollaborator->email));

        $this->withSession(['verifiedEmails' => ['test@example-1qw23er4.no']])
        ->assertFalse($h5p->isCollaborator());
    }
}
