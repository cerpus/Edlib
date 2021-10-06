<?php

namespace App\Console\Commands;

use App\Libraries\H5P\H5PArtisan;
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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Storage $storage)
    {
        $h5pArtisan = new H5PArtisan($storage, $this);
        $h5pArtisan->addPresaveToLibraries();
    }
}
