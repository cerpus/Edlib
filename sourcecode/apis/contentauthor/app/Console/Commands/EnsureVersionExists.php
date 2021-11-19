<?php

namespace App\Console\Commands;

use App\Article;
use App\H5PContent;
use Illuminate\Console\Command;
use Cerpus\VersionClient\VersionClient;
use Illuminate\Support\Facades\Log;

class EnsureVersionExists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cerpus:ensure-version {offset=0} {--skip-article} {--dry-run} {--debug}';

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
     *
     * @return mixed
     */
    public function handle()
    {
        $h5pCount = 0;
        $articleCount = 0;

        $inputOffset = $this->argument('offset');
        $offset = 0;
        if (is_numeric($inputOffset)) {
            $offset = (int) $inputOffset;
        }

        set_time_limit(0);

        if ($this->option("debug")) {
            $this->info("Running in debug mode");
        }

        if (!$this->option("skip-article")) {
            Article::where("id", ">=", $offset)->chunkById(250, function ($articles) use (&$articleCount) {
                $this->debug("Found " . count($articles) . " articles");
                $lastId = 0;
                $articles->each(function ($article) use (&$articleCount, &$lastId) {
                    $this->debug("Working on article id $article->id");
                    $lastId = $article->id;

                    if ($article->version_id == null) {
                        $this->debug("Article has no version");
                        return;
                    }

                    $vc = app(VersionClient::class);
                    $vc->getVersion($article->version_id);

                    if ($vc->getErrorCode() == null) {
                        $this->debug("Article is good");
                        return;
                    }

                    if ($vc->getErrorCode() == 404) {
                        $this->warn("Couldn't find version for article: $article->version_id, $article->title, $article->updated_at. Updating version id to null");
                        if (!$this->option("dry-run")) {
                            $article->version_id = null;
                            $article->save();
                        }
                        $articleCount = $articleCount + 1;
                    } else {
                        $this->error("Something happened while retrieving article version: $article->version_id, $article->title, $article->updated_at");
                    }
                });

                $this->info("Article progress: $lastId");
            });

            $offset = 0;
        } else {
            $this->debug("Skipping articles");
        }

        H5PContent::where("id", ">=", $offset)->chunkById(250, function ($h5ps) use (&$h5pCount) {
            $lastId = 0;
            $h5ps->each(function ($h5p) use (&$h5pCount, &$lastId) {
                $this->debug("Working on h5p id $h5p->id");
                $lastId = $h5p->id;

                if ($h5p->version_id == null) {
                    $this->debug("H5P has no version");
                    return;
                }

                $vc = app(VersionClient::class);
                $vc->getVersion($h5p->version_id);

                if ($vc->getErrorCode() == null) {
                    $this->debug("H5P is good");
                    return;
                }

                if ($vc->getErrorCode() == 404) {
                    $this->warn("Couldn't find version for h5p: $h5p->version_id, $h5p->title, $h5p->updated_at. Updating version id to null");
                    if (!$this->option("dry-run")) {
                        $h5p->version_id = null;
                        $h5p->save();
                    }
                    $h5pCount = $h5pCount + 1;
                } else {
                    $this->error("Something happened while retrieving h5p version: $h5p->version_id, $h5p->title, $h5p->updated_at");
                }
            });

            $this->info("H5p progress: $lastId");
        });

        $this->info("Couldn't find version for $h5pCount h5ps and $articleCount articles");
    }
}
