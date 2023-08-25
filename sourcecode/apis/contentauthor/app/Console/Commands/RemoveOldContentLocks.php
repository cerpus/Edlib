<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\ContentLock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveOldContentLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cerpus:remove-content-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old content locks';

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
     */
    public function handle()
    {
        // The MySQL replication in test and prod can have problems
        // with transactions that does not use primary keys.
        $staleLocks = ContentLock::select('content_id')
            ->where('updated_at', '<', Carbon::now()->subMinutes(ContentLock::EXPIRES))
            ->get()
            ->pluck('content_id')
            ->all();

        if (!empty($staleLocks)) {
            ContentLock::whereIn('content_id', $staleLocks)->delete();
            Log::info("Removed " . count($staleLocks) . " stale locks.");
        } else {
            Log::debug("No stale locks removed.");
        }
    }
}
