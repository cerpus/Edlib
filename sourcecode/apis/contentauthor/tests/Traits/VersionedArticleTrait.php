<?php
namespace Tests\Traits;

use Illuminate\Support\Facades\Storage;
use App\File;
use App\Article;
use Ramsey\Uuid\Uuid;

trait VersionedArticleTrait
{
    protected $article_uploads = 'article-uploads';
    protected $h5p_uploads = 'h5p-uploads';
    protected $originalArticle;

    public function setUpOriginalArticle($params = [], $license = 'PRIVATE', $copyable = false)
    {
        //Create the original article with a file attached
        $articleId = Uuid::uuid4()->toString();
        $createParams = array_merge([
            'content' => '
<p><img src="/' . $articleId . '/tree.jpg" style="height:80px; width:454px" /></p>
<p><img src="/' . $articleId . '/tree2.jpg" style="height:80px; width:454px" /></p>
<p>&nbsp;</p>
'
        ], $params);

        $this->originalArticle = Article::factory()->create($createParams);
        $originalFile = DIRECTORY_SEPARATOR . $this->originalArticle->id . DIRECTORY_SEPARATOR . 'tree.jpg';
        $fromFile = base_path() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'tree.jpg';

        Storage::disk($this->article_uploads)->put($originalFile, file_get_contents($fromFile));

        $file = new File();
        $file->name = 'tree.jpg';
        $file->original_name = 'tree.jpg';
        $file->size = Storage::disk($this->article_uploads)->size($originalFile);
        $file->mime = 'image/jpeg';

        $this->originalArticle->files()->save($file);

        $this->setUpLicensing($license, $copyable);

    }

    public function removeDirectories()
    {
        // Remove directories
        $directories = Storage::disk($this->article_uploads)->directories('/');
        collect($directories)->each(function ($directory) {
            Storage::disk($this->article_uploads)->deleteDirectory($directory);
        });
    }
}
