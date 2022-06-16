<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentAssetController
{
    public function __invoke($path, Request $request)
    {
        if (!Storage::disk()->exists($path)) {
            throw new NotFoundHttpException('File not found');
        }

        $detector = new FinfoMimeTypeDetector();
        $response = new StreamedResponse;
        $filename = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', basename($path));

        $response->headers->replace([
            'ETag' => md5($path . request()->input('ver')),
            'Cache-Control' => 'public, max-age=604800, immutable',
            'Content-Type' => $detector->detectMimeTypeFromPath($path),
            'Content-Length' => Storage::disk()->size($path),
            'Content-Disposition' => $response->headers->makeDisposition(
                'inline', $filename
            ),
        ]);

        $response->setCallback(function () use ($path) {
            $stream = Storage::disk()->readStream($path);
            fpassthru($stream);
            fclose($stream);
        });

        return $response;
    }
}
