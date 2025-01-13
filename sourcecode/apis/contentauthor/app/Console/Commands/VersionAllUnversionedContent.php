<?php

namespace App\Console\Commands;

use App\Article;
use App\Content;
use App\ContentVersion;
use App\H5PContent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VersionAllUnversionedContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cerpus:init-versioning {--dry-run}';

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
     */
    public function handle()
    {
        set_time_limit(0);

        Article::unversioned()
            ->orderBy('created_at')
            ->chunk(250, function ($articles) {
                $articles->each(function (Article $article) {
                    $versionId = $this->createVersion(
                        $article->id,
                        Content::TYPE_ARTICLE,
                        $article->owner_id,
                        $article->updated_at->timestamp,
                    );
                    if (!$this->option('dry-run')) {
                        DB::update('UPDATE articles SET version_id = ? WHERE id = ?', [
                            $versionId,
                            $article->id,
                        ]);
                    }
                    $this->info("Article: $versionId, $article->title, $article->updated_at");
                });
            });

        H5PContent::unversioned()
            ->orderBy('id')
            ->chunk(250, function ($h5ps) {
                $h5ps->each(function (H5PContent $h5p) {
                    $versionId = $this->createVersion(
                        $h5p->id,
                        Content::TYPE_H5P,
                        $h5p->user_id,
                        $h5p->updated_at->timestamp,
                    );
                    if (!$this->option('dry-run')) {
                        DB::update('UPDATE h5p_contents SET version_id = ? WHERE id = ?', [
                            $versionId,
                            $h5p->id,
                        ]);
                    }

                    $this->info("H5P: $versionId, $h5p->title, $h5p->updated_at");
                });
            });
    }

    private function createVersion($contentId, $contentType, $ownerId, $timestamp): string
    {
        $versionId = Str::orderedUuid()->toString();
        if (!$this->option('dry-run')) {
            $result = DB::insert('INSERT INTO content_versions (id, content_id, content_type, created_at, version_purpose, user_id, linear_versioning) values (?,?,?,?,?,?,?)', [
                $versionId,
                $contentId,
                $contentType,
                Carbon::createFromTimestamp($timestamp)->format('Y-m-d H:i:s.u'),
                ContentVersion::PURPOSE_INITIAL,
                $ownerId,
                (bool) config('feature.linear-versioning'),
            ]);

            if (!$result) {
                throw new \RuntimeException(sprintf("Failed creating ContentVersions: ContentType: %s, ContentId: %s", $contentType, $contentId));
            }
        }

        return $versionId;
    }
}
