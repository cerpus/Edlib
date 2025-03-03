<?php

namespace Tests\Integration\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentAssetControllerTest extends TestCase
{
    public function testRedirectsToLocalFile(): void
    {
        $this->get('/content/assets/asset.jpg')
            ->assertRedirect('http://localhost/h5pstorage/asset.jpg');
    }

    public function testRedirectsToCdn(): void
    {
        Storage::fake(config: [
            'url' => 'https://my-cdn.example.com',
        ]);

        $this->get('/content/assets/asset.jpg')
            ->assertRedirect('https://my-cdn.example.com/asset.jpg');
    }
}
