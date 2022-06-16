<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\H5PLibrary;
use App\Libraries\H5P\H5pPresave;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PublishPresave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'h5p:addPresave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds the presave.js script to H5P libraries to calculate the max score before saving';

    public function __construct(
        private readonly H5pPresave $presave,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $uploadDisk = Storage::disk();

        H5PLibrary::whereIn('name', $this->presave->getAllLibrariesWithScripts())
            ->orderBy('name')
            ->get()
            ->each(function (H5PLibrary $library) use ($uploadDisk): void {
                $contents = $this->presave->getScriptContents($library->name);
                $destination = self::getDestination($library);

                if (!$uploadDisk->exists($destination)) {
                    $uploadDisk->put($destination, $contents);
                    $uploadDisk->prepend($destination, '//PresaveArtisan');
                    $this->info(sprintf("%s created.", $destination));
                } else {
                    $this->line(sprintf("%s already exists. Skipping.", $destination));
                }
            });
    }

    private static function getDestination(H5PLibrary $library): string
    {
        $directory = $library->getLibraryString(true);

        return "libraries/$directory/presave.js";
    }
}
