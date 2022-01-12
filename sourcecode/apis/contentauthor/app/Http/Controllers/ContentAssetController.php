<?php


namespace App\Http\Controllers;

use App\Libraries\ContentAuthorStorage;
use Illuminate\Http\Request;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentAssetController
{
    private ContentAuthorStorage $contentAuthorStorage;

    public function __construct(ContentAuthorStorage $contentAuthorStorage)
    {
        $this->contentAuthorStorage = $contentAuthorStorage;
    }

    public function __invoke($path, Request $request)
    {
        if (!$this->contentAuthorStorage->getBucketDisk()->exists($path)) {
            throw new NotFoundHttpException('File not found');
        }
        
        $detector = new FinfoMimeTypeDetector();
        $response = new StreamedResponse;
        $filename = basename($path);

        $response->headers->replace([
            'ETag' => md5($path . request()->input('ver')),
            'Cache-Control' => 'public, max-age=604800, immutable',
            'Content-Type' => $detector->detectMimeTypeFromPath($path),
            'Content-Length' => $this->contentAuthorStorage->getBucketDisk()->size($path),
            'Content-Disposition' => $response->headers->makeDisposition(
                'inline', $filename
            ),
        ]);

        $response->setCallback(function () use ($path) {
            $stream = $this->contentAuthorStorage->getBucketDisk()->readStream($path);
            fpassthru($stream);
            fclose($stream);
        });

        return $response;
    }
}
