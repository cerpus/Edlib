<?php

namespace App\Http\Controllers\API;

use App\H5PFile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class H5PFileUploadController extends Controller
{
    public function __invoke($requestId)
    {
        /** @var Collection $files */
        $files = H5PFile::ofFileUploadFromRequestId($requestId)->get();
        $responseValues = [
            'total' => $files->count(),
            'left' => $files->filter(function ($file) {
                return $file->state === H5PFile::FILE_CLONEFILE;
            })->count(),
            'failed' => $files->filter(function ($file) {
                return $file->state === H5PFile::FILE_FAILED;
            })->count(),
            'done' => $files->filter(function ($file) {
                return $file->state === H5PFile::FILE_READY;
            })->count(),
        ];
        return response()->json($responseValues, Response::HTTP_OK);
    }
}
