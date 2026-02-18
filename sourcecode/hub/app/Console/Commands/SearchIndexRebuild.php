<?php

namespace App\Console\Commands;

use App\Jobs\RebuildContentIndex;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;

class SearchIndexRebuild extends Command
{
    protected $signature = 'edlib:search-index-rebuild {--queue}';
    protected $description = 'Rebuild the Meilisearch index. Destroys existing index, updates settings and re-indexes the data. Listings and search will be empty or incomplete while re-indexing.';

    public function handle(Dispatcher $dispatcher): void
    {
        if ($this->confirm('While rebuilding the index, listings and search will not display any content! Continue?', false)) {
            if ($this->option('queue')) {
                $dispatcher->dispatch(new RebuildContentIndex());
                $this->info('The Meilisearch rebuild has been queued');
            } else {
                $this->info('Rebuilding Meilisearch index...');
                $dispatcher->dispatchSync(new RebuildContentIndex());
                $this->info('Done');
            }
        } else {
            $this->info('Cancelled');
        }
    }
}
