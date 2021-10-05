<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\ContentAssetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ContentAssetControllerTest extends TestCase
{
    private $testDisk, $fakedisk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDisk = Storage::disk('testDisk');
        $this->fakedisk = Storage::fake('h5p-uploads');
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
        Cache::shouldReceive('has')
            ->once()
            ->andReturnFalse();

        $this->expectException(NotFoundHttpException::class);
        (new ContentAssetController())->__invoke("not_valid_path", new Request());
    }

    /**
     * @test
     */
    public function pathToCachedAsset()
    {
        Cache::shouldReceive('has')
            ->twice()
            ->andReturns(false, true);
        Cache::shouldReceive('put')
            ->once();
        Cache::shouldReceive('get')
            ->once()
            ->andReturn([
                'content' => "body {\n    background-color: red;\n}\n",
                "headers" => []
            ]);

        $this->linkCachedAssetsFolder();
        $path = "cachedassets/my_cached_asset.css";
        $request = Request::create($path);
        $response = (new ContentAssetController())->__invoke($path, $request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("body {
    background-color: red;
}
", $response->getContent());
    }
}
