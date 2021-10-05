<?php

namespace App\Libraries\H5P;


use App\H5PLibrary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class H5PArtisan
{
    private $uploadDisk, $librariesDisk;
    private $presaveDirectories;
    private $command;

    public function __construct(Storage $storage, Command $command)
    {
        $this->librariesDisk = $storage::disk('h5p');
        $this->uploadDisk = $storage::disk('h5p-uploads');
        $this->command = $command;
    }

    public function addPresaveToLibraries()
    {
        $this->getPresaveFolders();
        H5PLibrary::whereIn('name', $this->presaveDirectories->keys())
            ->orderBy('name')
            ->get()
            ->each(function ($library) {
                $fileName = $this->getSourceFile($library->name);
                $presaveSource = $this->librariesDisk->get($fileName);
                $copyPath = $this->getCopyPath($library->getLibraryString(true));
                if (!$this->uploadDisk->exists($copyPath)) {
                    $this->uploadDisk->put($copyPath, $presaveSource);
                    $this->uploadDisk->prepend($copyPath, '//PresaveArtisan');
                    $this->command->info(sprintf("%s created.", $copyPath));
                } else {
                    $this->command->line(sprintf("%s already exists. Skipping.", $copyPath));
                }
            });
    }

    private function getPresaveFolders()
    {
        $this->presaveDirectories = collect($this->librariesDisk->directories("Presave"))
            ->mapWithKeys(function ($library) {
                $split = explode(DIRECTORY_SEPARATOR, $library);
                return [array_pop($split) => $library];
            });
    }

    private function getSourceFile($libraryName)
    {
        return $this->presaveDirectories->get($libraryName) . DIRECTORY_SEPARATOR . 'presave.js';
    }

    private function getCopyPath($h5pDirectory)
    {
        return implode(DIRECTORY_SEPARATOR, [
            'libraries',
            $h5pDirectory,
            'presave.js'
        ]);
    }
}