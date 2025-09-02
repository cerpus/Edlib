<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;

class MigrateTags extends Command
{
    protected $signature = 'edlib:migrate-tags {--queue}';

    protected $description = 'Migrate old tags';

    public function handle(Dispatcher $dispatcher): void
    {
        if ($this->option('queue')) {
            $dispatcher->dispatch(new \App\Jobs\MigrateTags());
            $this->info('The migration has been queued');

            return;
        }

        $this->info('Migrating tags...');
        $dispatcher->dispatchSync(new \App\Jobs\MigrateTags());
        $this->info('Done.');
    }
}
