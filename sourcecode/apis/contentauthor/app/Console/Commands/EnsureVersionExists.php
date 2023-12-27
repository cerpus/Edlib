<?php

namespace App\Console\Commands;

use App\Content;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EnsureVersionExists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cerpus:ensure-version {--skip-article} {--skip-h5p} {--dry-run} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this to ensure version actually exists.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function debug($message)
    {
        if ($this->option("debug")) {
            $this->info("<fg=blue>$message</>");
        }
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $h5pCount = 0;
        $articleCount = 0;

        set_time_limit(0);

        if ($this->option("debug")) {
            $this->info("Running in debug mode");
        }

        if (!$this->option("skip-article")) {
            DB::table('articles AS a')
                ->select(['a.id AS article_id', 'a.version_id', 'cv.id AS content_version_id'])
                ->leftJoin('content_versions AS cv', function (JoinClause $join) {
                    $join->on('cv.content_id', '=', 'a.id')->where('cv.content_type', '=', Content::TYPE_ARTICLE);
                })
                ->orderBy('a.created_at')
                ->chunk(100, function (Collection $rows) use (&$articleCount) {
                    $this->debug("Processing chunk with " . count($rows) . " articles");
                    foreach ($rows as $row) {
                        if ($row->version_id && $row->content_version_id) {
                            $this->info(sprintf('Article %s is good', $row->article_id));
                        } elseif ($row->version_id === null && $row->content_version_id === null) {
                            $this->info(sprintf('Article %s is not versioned', $row->article_id));
                        } elseif ($row->version_id !== null && $row->content_version_id === null) {
                            $this->info(sprintf('Article %s has missing version %s', $row->article_id, $row->version_id));
                            if (!$this->option('dry-run')) {
                                $this->warn(' - Setting version id to null');
                                DB::update('UPDATE articles SET version_id = NULL WHERE id = ? LIMIT 1', [
                                    $row->article_id,
                                ]);
                            }
                            $articleCount++;
                        } elseif ($row->version_id === null && $row->content_version_id !== null) {
                            $this->info(sprintf('Article %s has unconnected version %s', $row->article_id, $row->content_version_id));
                            if (!$this->option('dry-run')) {
                                $this->info(' - Updating version id to ' . $row->content_version_id);
                                DB::update('UPDATE articles SET version_id = ? WHERE id = ? LIMIT 1', [
                                    $row->content_version_id,
                                    $row->article_id,
                                ]);
                            }
                        }
                    }
                });
        } else {
            $this->debug("Skipping articles");
        }

        if (!$this->option('skip-h5p')) {
            DB::table('h5p_contents AS h')
                ->select(['h.id AS h5p_id', 'h.version_id', 'cv.id AS content_version_id'])
                ->leftJoin('content_versions AS cv', function (JoinClause $join) {
                    $join->on('cv.content_id', '=', 'h.id')->where('cv.content_type', '=', Content::TYPE_H5P);
                })
                ->orderBy('h.id')
                ->chunkById(100, function (Collection $rows) use (&$h5pCount) {
                    $this->debug('Processing chunk with ' . count($rows) . ' H5Ps');
                    foreach ($rows as $row) {
                        if ($row->version_id && $row->content_version_id) {
                            $this->info(sprintf('H5P %s is good', $row->h5p_id));
                        } elseif ($row->version_id === null && $row->content_version_id === null) {
                            $this->info(sprintf('H5P %s is not versioned', $row->h5p_id));
                        } elseif ($row->version_id !== null && $row->content_version_id === null) {
                            $this->info(sprintf('H5P %s has missing version %s', $row->h5p_id, $row->version_id));
                            if (!$this->option('dry-run')) {
                                $this->warn(' - Setting version id to null');
                                DB::update('UPDATE h5p_contents SET version_id = NULL WHERE id = ? LIMIT 1', [
                                    $row->h5p_id,
                                ]);
                            }
                            $h5pCount++;
                        } elseif ($row->version_id === null && $row->content_version_id !== null) {
                            $this->info(sprintf('H5P %s has unconnected version %s', $row->h5p_id, $row->content_version_id));
                            if (!$this->option('dry-run')) {
                                $this->info(' - Updating version id to ' . $row->content_version_id);
                                DB::update('UPDATE h5p_contents SET version_id = ? WHERE id = ? LIMIT 1', [
                                    $row->content_version_id,
                                    $row->h5p_id,
                                ]);
                            }
                        }
                    }
                }, 'h.id', 'h5p_id');
        } else {
            $this->debug('Skipping H5Ps');
        }

        $this->info("Couldn't find version for $h5pCount h5ps and $articleCount articles");
    }
}
