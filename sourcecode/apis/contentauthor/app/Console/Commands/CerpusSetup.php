<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CerpusSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cerpus:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install/setup components needed for the application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('vendor:publish', ['--provider' => 'Intervention\Image\ImageServiceProviderLaravel5']);
    }
}
