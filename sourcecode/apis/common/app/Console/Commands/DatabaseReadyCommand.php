<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class DatabaseReadyCommand extends Command
{
    protected $signature = 'app:db-ready';

    protected $description = 'Exit with code 0 when DB is ready, 1 otherwise';

    public function handle(): int
    {
        try {
            DB::statement('SELECT 1');

            return 0;
        } catch (QueryException) {
            return 1;
        }
    }
}
