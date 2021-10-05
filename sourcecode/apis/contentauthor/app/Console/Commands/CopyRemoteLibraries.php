<?php

namespace App\Console\Commands;

use App\Libraries\H5P\Helper\RClone;
use Illuminate\Console\Command;

class CopyRemoteLibraries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cerpus:copy-remote-libraries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copies libraries from remote storage to local file storage';

    /**
     * CopyRemoteLibraries constructor.
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @param RClone $clone
     * @return mixed
     */
    public function handle(RClone $clone)
    {
        return $clone->handleLibraryCopy();
    }

}
