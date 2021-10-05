<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use League\Flysystem\FileNotFoundException;

class ContentAssetController
{

    /** @var Request */
    private $request;

    public function __invoke($path, Request $request)
    {
        $this->request = $request;
        try {
            $cacheKey = $this->getCacheKey($path . $request->input('ver'));
            if( Cache::has($cacheKey)){
                return $this->returnCachedContent($cacheKey);
            }

            $disk = config('app.useContentCloudStorage') ? Storage::cloud() : Storage::disk(config('h5p.H5PStorageDisk'));
            if( $disk->exists($path) && $this->isCacheTarget($path)){
                $fileMetadata = $disk->getMetadata($path);
                $contentToCache = [
                    'headers' => [
                        'ETag' => md5($path . request()->input('ver')),
                        'Cache-Control' => 'public, max-age=604800, immutable',
                        'Content-Length' => $fileMetadata['size'],
                        'Content-Type' => $fileMetadata['mimetype'] ?? "text/plain",
                        'Content-Disposition' => sprintf('inline; filename=%s', basename($path)),
                        'Last-Modified' => $fileMetadata['timestamp'],
                    ],
                    'content' => $disk->get($path)
                ];
                Cache::put($cacheKey, $contentToCache, config('cache.ttl.assets', 3600));
                if( Cache::has($cacheKey)){
                    return $this->returnCachedContent($cacheKey);
                }
            }

            return $disk->response($path, null, [
                'ETag' => md5($path . request()->input('ver')),
                'Cache-Control' => 'public, max-age=604800, immutable',
            ]);
        } catch (FileNotFoundException $fileNotFoundException){
            abort(Response::HTTP_NOT_FOUND);
        }
    }

    private function getCacheKey($key)
    {
        return config('cache.prefix') . "_cached_assets_" . $key;
    }

    private function isCacheTarget($path): bool
    {
        if ($this->request->is("*.js", "*.css") || preg_match('/^(\/?article-uploads\/|\/?libraries\/|content\/\d+\/(?:images|audios|files))/', $path) ){
            return true;
        }
        return false;
    }

    private function returnCachedContent($cacheKey)
    {
        $content = Cache::get($cacheKey);
        return \response($content['content'], Response::HTTP_OK, $content['headers']);
    }

}
