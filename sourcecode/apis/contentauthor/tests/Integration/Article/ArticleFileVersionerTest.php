<?php

namespace Tests\Integration\Article;

use App\Article;
use App\File;
use App\Http\Libraries\ArticleFileVersioner;
use App\Libraries\DataObjects\ContentStorageSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Helpers\ContentAuthorStorageTrait;

class ArticleFileVersionerTest extends TestCase
{
    use RefreshDatabase, ContentAuthorStorageTrait;

    protected $originalArticle, $newArticle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpContentAuthorStorage();
    }

    public function setUpOriginal()
    {
        //Create the original article with a file attached
        $articleId = Str::uuid();
        $this->originalArticle = Article::factory()->create([
            'id' => $articleId,
            'content' => '
<p><img src="/' . $articleId . '/tree.jpg" style="height:80px; width:454px" /></p>
<p><img src="/' . $articleId . '/tree2.jpg" style="height:80px; width:454px" /></p>
<p>&nbsp;</p>
'
        ]);
        $originalFile = sprintf(ContentStorageSettings::ARTICLE_FILE, $this->originalArticle->id, 'tree.jpg');
        $fromFile = base_path('tests/files/tree.jpg');

        $this->contentAuthorStorage->getBucketDisk()->put($originalFile, file_get_contents($fromFile));

        $file = new File();
        $file->name = 'tree.jpg';
        $file->original_name = 'tree.jpg';
        $file->size = $this->contentAuthorStorage->getBucketDisk()->size($originalFile);
        $file->mime = 'image/jpeg';

        $this->originalArticle->files()->save($file);
    }

    public function tearDown(): void
    {
        // Remove directories
        $directories = $this->contentAuthorStorage->getBucketDisk()->directories('/');
        collect($directories)->each(function ($directory) {
            $this->contentAuthorStorage->getBucketDisk()->deleteDirectory($directory);
        });
    }

    public function testSetup()
    {
        $this->setUpOriginal();
        $this->newArticle = Article::factory()->create([
            'parent_id' => $this->originalArticle->id,
            'parent_version_id' => $this->originalArticle->version_id,
        ]);

        $originalFileCount = $this->originalArticle->files()->count();
        $this->assertEquals(1, $originalFileCount);
        $originalPath = '/article-uploads/' . $this->originalArticle->id;
        $this->assertCount(1, $this->contentAuthorStorage->getBucketDisk()->files($originalPath));
    }

    public function testFileCopy()
    {
        $this->setUpOriginal();
        $this->newArticle = Article::factory()->create([
            'parent_id' => $this->originalArticle->id,
            'parent_version_id' => $this->originalArticle->version_id,
        ]);
        $articleFileVersioner = new ArticleFileVersioner($this->originalArticle, $this->newArticle);
        $articleFileVersioner->copy();
        $this->assertTrue($this->contentAuthorStorage->getBucketDisk()->has('/article-uploads/' . $this->originalArticle->id . '/tree.jpg'));
        $this->assertTrue($this->contentAuthorStorage->getBucketDisk()->has('/article-uploads/' . $this->newArticle->id . '/tree.jpg'));
    }

    public function testDatabaseUpdate()
    {
        $this->setUpOriginal();
        $this->newArticle = Article::factory()->create([
            'parent_id' => $this->originalArticle->id,
            'parent_version_id' => $this->originalArticle->version_id,
        ]);
        $articleFileVersioner = new ArticleFileVersioner($this->originalArticle, $this->newArticle);
        $articleFileVersioner->copy()->updateDatabase();

        $this->newArticle->fresh();

        $this->assertCount(1, $this->newArticle->files);
    }

    public function testRewriteFilePaths()
    {
        $this->setUpOriginal();
        $this->newArticle = Article::factory()->create([
            'content' => $this->originalArticle->content,
            'parent_id' => $this->originalArticle->id,
            'parent_version_id' => $this->originalArticle->version_id,
        ]);
        $articleFileVersioner = new ArticleFileVersioner($this->originalArticle, $this->newArticle);
        $newArticle = $articleFileVersioner->copy()->updateDatabase()->rewriteFilePath()->getNewArticle();

        $this->assertCount(1, $newArticle->files);
        $this->assertTrue($this->contentAuthorStorage->getBucketDisk()->has('/article-uploads/' . $newArticle->id . '/tree.jpg'));
        $this->assertNotEquals($newArticle->content, $this->originalArticle->content);
        $this->assertNotFalse(strstr($this->originalArticle->content, '/' . $this->originalArticle->id . '/'));
        $this->assertNotFalse(strstr($newArticle->content, '/' . $newArticle->id . '/'));
        $this->assertFalse(strstr($newArticle->content, '/' . $this->originalArticle->id . '/'));
    }
}
