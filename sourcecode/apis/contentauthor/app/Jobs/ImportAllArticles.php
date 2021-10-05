<?php

namespace App\Jobs;

use DB;
use App;
use Illuminate\Bus\Queueable;
use App\NdlaArticleImportStatus;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Libraries\NDLA\Importers\APIArticleImporter;

class ImportAllArticles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        set_time_limit(0);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('ndla_article_ids')
            ->when(!App::environment('production'), function ($query) {
                return $query->where('id', '<=', 100);
            })
            ->orderBy('id')->chunk(50, function ($articles) {
                foreach ($articles as $article) {
                    $importer = resolve(APIArticleImporter::class)->updateOnDuplicate();
                    $articleJson = json_decode($article->json);

                    NdlaArticleImportStatus::addStatus($articleJson->id, 'Starting import');

                    $start = microtime(true);
                    $importer->import($articleJson, true);
                    $importTime = sprintf("%0.2f", (microtime(true) - $start) * 1000);

                    NdlaArticleImportStatus::addStatus($articleJson->id, "{$articleJson->title->title} imported after $importTime ms");
                }
            });
    }
}
