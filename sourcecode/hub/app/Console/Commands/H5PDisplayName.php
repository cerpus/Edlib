<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Config;

class H5PDisplayName extends Command
{
    protected $signature = 'edlib:h5p-displayname {--queue} {--migrate}';

    protected $description = 'H5P Content type display value. Either content type machine name or content type title. Configure with environment setting `FEATURE_CA_CONTENT_TYPE_DISPLAY`';

    public function handle(Dispatcher $dispatcher): void
    {
        $displaytype = Config::get('features.ca-content-type-display');
        $this->output->write("<info>Configured value is: </info>" . ($displaytype ? "<comment>$displaytype</comment>" : "<error> - Not set -</error>"), newline: true);
        match($displaytype) {
            'h5p' => $this->info('Content type machine name is displayed as content type'),
            'h5p_title' => $this->info('Content type title is displayed as content type'),
            default => $this->info('Value is unset or invalid. Default is displaying content type machine name as content type'),
        };

        if ($this->option('migrate')) {
            if ($this->option('queue')) {
                $dispatcher->dispatch(new \App\Jobs\SwapH5PTypeDisplayName());
                $this->info('The migration has been queued');

                return;
            }

            $this->info('Migrating content type display value...');
            $dispatcher->dispatchSync(new \App\Jobs\SwapH5PTypeDisplayName());
            $this->info('Done.');
        }
    }
}
