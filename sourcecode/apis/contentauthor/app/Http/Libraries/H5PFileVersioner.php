<?php

namespace App\Http\Libraries;

use App\H5PContent;
use Illuminate\Support\Facades\Storage;

class H5PFileVersioner
{
    protected $originalH5P;
    protected $newH5P;

    public function __construct(H5PContent $originalH5P, H5PContent $newH5P)
    {
        $this->originalH5P = $originalH5P;
        $this->newH5P = $newH5P;
    }

    public function copy()
    {
        $originalPath = "/content/{$this->originalH5P->id}";
        $storage = Storage::disk();

        //Create all directories
        collect($storage->allDirectories($originalPath))
            ->each(function ($originalDirectory) use ($storage) {
                $theNewDirectory = str_replace($this->originalH5P->id, $this->newH5P->id, $originalDirectory);
                $storage->makeDirectory($theNewDirectory);
            });

        // Copy all files
        collect($storage->allFiles($originalPath))
            ->each(function ($theOriginalFile) use ($storage) {
                $theNewFile = str_replace($this->originalH5P->id, $this->newH5P->id, $theOriginalFile);
                $storage->copy($theOriginalFile, $theNewFile);
            });

        return $this;
    }
}
