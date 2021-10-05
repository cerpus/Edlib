<?php

namespace App\Console\Commands;

use App\Article;
use App\H5PContent;
use Illuminate\Console\Command;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;

class VersionAllUnversionedContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cerpus:init-versioning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this to version all unversioned content when we start versioning.';

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
        set_time_limit(0);
        Article::unversioned()->orderBy('id')->chunk(250, function ($articles) {
            $articles->each(function ($article) {
                $vd = app(VersionData::class);
                $vd->setUserId($article->owner_id)
                    ->setExternalReference($article->id)
                    ->setExternalSystem(config('app.site-name'))
                    ->setExternalUrl(route('article.show', $article->id))
                    ->setCreatedAt($article->updated_at->timestamp)
                    ->setVersionPurpose(VersionData::INITIAL);

                $vc = app(VersionClient::class);
                $version = $vc->initialVersion($vd);

                if (is_object($version) && method_exists($version, 'getId')) {
                    $article->version_id = $version->getId();
                    $article->save();
                }

                echo "Article: $article->version_id, $article->title, $article->updated_at<br>\n";
            });
        });

        H5PContent::unversioned()->orderBy('id')->chunk(250, function ($h5ps) {
            $h5ps->each(function ($h5p) {
                $vd = app(VersionData::class);
                $vd->setUserId($h5p->owner_id)
                    ->setExternalReference($h5p->id)
                    ->setExternalSystem(config('app.site-name'))
                    ->setExternalUrl(route('h5p.show', $h5p->id))
                    ->setCreatedAt($h5p->updated_at->timestamp)
                    ->setVersionPurpose(VersionData::INITIAL);

                $vc = app(VersionClient::class);
                $start = microtime(true);
                $version = $vc->initialVersion($vd);
                $time = microtime(true) - $start;
                if (is_object($version) && method_exists($version, 'getId')) {
                    $h5p->version_id = $version->getId();
                    $h5p->save();
                }

                echo "H5P: Time: $time   | $h5p->version_id | $h5p->title | $h5p->updated_at<br>\n";
            });
        });
    }
}
