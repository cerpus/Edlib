<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EdlibLicenseStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edlib:license-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List licenses used by Articles, Games, Links, Question sets and H5P contents';

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
    public function handle(): int
    {
        $this->showCount('Articles', 'articles');
        $this->showCount('Games', 'games');
        $this->showCount('Links', 'links');
        $this->showCount('Question sets', 'question_sets');
        $this->showCount('H5P contents', 'h5p_contents');

        return 0;
    }

    public function showCount(string $section, string $tableName): void
    {
        $this->line($section);
        $items = DB::table($tableName)
            ->selectRaw('license, count(license) as aggregate')
            ->groupBy('license')
            ->orderBy('license')
            ->get()
            ->map(fn($item) => [
                'license' => $item->license,
                'count' => $item->aggregate,
            ])
            ->toArray();

        $this->table(['License', 'Count'], $items);
        $this->newLine();
    }
}
