<?php

namespace Tests\Article;

use App\File;
use App\Article;
use Tests\TestCase;
use Illuminate\Support\Str;
use Tests\Traits\MockLicensingTrait;
use Illuminate\Support\Facades\Storage;
use App\Http\Libraries\ArticleFileVersioner;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleFileVersionerTest extends TestCase
{
    use RefreshDatabase, MockLicensingTrait;

    protected $disk = 'article-uploads';

    protected $originalArticle, $newArticle;

    public function setUpOriginal()
    {
        //Create the original article with a file attached
        $articleId = Str::uuid();
        $this->originalArticle = factory(Article::class)->create([
            'id' => $articleId,
            'content' => '
<p><img src="/' . $articleId . '/tree.jpg" style="height:80px; width:454px" /></p>
<p><img src="/' . $articleId . '/tree2.jpg" style="height:80px; width:454px" /></p>
<p>&nbsp;</p>
'
        ]);
        $originalFile = DIRECTORY_SEPARATOR . $this->originalArticle->id . DIRECTORY_SEPARATOR . 'tree.jpg';
        $fromFile = base_path() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'tree.jpg';

        Storage::disk($this->disk)->put($originalFile, file_get_contents($fromFile));

        $file = new File();
        $file->name = 'tree.jpg';
        $file->original_name = 'tree.jpg';
        $file->size = Storage::disk($this->disk)->size($originalFile);
        $file->mime = 'image/jpeg';

        $this->originalArticle->files()->save($file);
    }

    public function tearDown(): void
    {
        // Remove directories
        $directories = Storage::disk($this->disk)->directories('/');
        collect($directories)->each(function ($directory) {
            Storage::disk($this->disk)->deleteDirectory($directory);
        });
    }

    public function testSetup()
    {
        $this->setUpOriginal();
        $this->newArticle = factory(Article::class)->create([
            'parent_id' => $this->originalArticle->id,
            'parent_version_id' => $this->originalArticle->version_id,
        ]);

        $originalFileCount = $this->originalArticle->files()->count();
        $this->assertEquals(1, $originalFileCount);
        $originalPath = DIRECTORY_SEPARATOR . $this->originalArticle->id;
        $this->assertCount(1, Storage::disk($this->disk)->files($originalPath));
    }

    public function testFileCopy()
    {
        $this->setUpOriginal();
        $this->newArticle = factory(Article::class)->create([
            'parent_id' => $this->originalArticle->id,
            'parent_version_id' => $this->originalArticle->version_id,
        ]);
        $articleFileVersioner = new ArticleFileVersioner($this->originalArticle, $this->newArticle);
        $articleFileVersioner->copy();
        $this->assertTrue(Storage::disk($this->disk)->has('/' . $this->originalArticle->id . '/tree.jpg'));
        $this->assertTrue(Storage::disk($this->disk)->has('/' . $this->newArticle->id . '/tree.jpg'));
    }

    public function testDatabaseUpdate()
    {
        $this->setUpOriginal();
        $this->newArticle = factory(Article::class)->create([
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
        $this->newArticle = factory(Article::class)->create([
            'content' => $this->originalArticle->content,
            'parent_id' => $this->originalArticle->id,
            'parent_version_id' => $this->originalArticle->version_id,
        ]);
        $articleFileVersioner = new ArticleFileVersioner($this->originalArticle, $this->newArticle);
        $newArticle = $articleFileVersioner->copy()->updateDatabase()->rewriteFilePath()->getNewArticle();

        $this->assertCount(1, $newArticle->files);
        $this->assertTrue(Storage::disk($this->disk)->has('/' . $newArticle->id . '/tree.jpg'));
        $this->assertNotEquals($newArticle->content, $this->originalArticle->content);
        $this->assertNotFalse(strstr($this->originalArticle->content, '/' . $this->originalArticle->id . '/'));
        $this->assertNotFalse(strstr($newArticle->content, '/' . $newArticle->id . '/'));
        $this->assertFalse(strstr($newArticle->content, '/' . $this->originalArticle->id . '/'));
    }
}
