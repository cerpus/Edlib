<?php

namespace Tests\Integration\Http\Controllers;

use App\Http\Controllers\ContentAssetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Helpers\ContentAuthorStorageTrait;
use Tests\TestCase;

class ContentAssetControllerTest extends TestCase
{
    use ContentAuthorStorageTrait;
    private $testDisk, $fakedisk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpContentAuthorStorage();
        $this->testDisk = Storage::disk('testDisk');
        $this->fakedisk = Storage::fake();
        config([
            'h5p.storage.path' => $this->fakedisk->path(""),
        ]);
    }

    private function linkCachedAssetsFolder()
    {
        symlink($this->testDisk->path('files/cachedassets'), $this->fakedisk->path('cachedassets'));
    }

    /**
     * @test
     */
    public function nonExistingCachedAsset()
    {
        $this->expectException(NotFoundHttpException::class);
        (new ContentAssetController($this->contentAuthorStorage))->__invoke("not_valid_path", new Request());
    }

    /**
     * @test
     */
    public function pathToCachedAsset()
    {
        $this->linkCachedAssetsFolder();
        $path = "cachedassets/my_cached_asset.css";
        $request = Request::create($path);
        $response = (new ContentAssetController($this->contentAuthorStorage))->__invoke($path, $request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('inline; filename=my_cached_asset.css', $response->headers->get('Content-Disposition'));
    }
}
