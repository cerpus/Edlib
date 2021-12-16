<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentAssetController
{
    public function __invoke($path, Request $request)
    {
        $disk = config('app.useContentCloudStorage') ? Storage::cloud() : Storage::disk(config('h5p.H5PStorageDisk'));
        if (!$disk->exists($path)) {
            throw new NotFoundHttpException('File not found');
        }

        return $disk->response($path, null, [
            'ETag' => md5($path . request()->input('ver')),
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }
}
