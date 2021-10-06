<?php

namespace App\Jobs;

use App\Article;
use App\Norgesfilm;
use App\NdlaIdMapper;
use Illuminate\Bus\Queueable;
use App\NdlaArticleImportStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Libraries\NDLA\Traits\NdlaUrlHelper;

class PopulateNorgesfilm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NdlaUrlHelper;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        NdlaArticleImportStatus::logDebug(14, "Norgesfilm: Starting population");

        NdlaArticleImportStatus::logDebug(14, "Norgesfilm: Emptying table.");
        DB::table('norgesfilms')->truncate();

        app(NdlaIdMapper::class)
            ->article()
            ->chunk(100, function ($mappers) {
                $mappers->each(function ($m) {
                    if ($article = Article::find($m->ca_id)) {
                        $matches = [];
                        preg_match('/ndla\.filmiundervisning\.no\/film\/ndlafilm\.aspx/', $article->content, $matches);
                        if ($matches) {
                            $ndlaUrl = $this->fetchNewNdlaUrl($m);
                            Norgesfilm::updateOrCreate(['article_id' => $article->id],
                                [
                                    'article_title' => $article->title,
                                    'article_url' => route('article.show', $article->id, false),
                                    'ndla_id' => $m->ndla_id,
                                    'ndla_url' => $ndlaUrl,
                                ]);

                            NdlaArticleImportStatus::logDebug(14, "Norgesfilm: Added '{$article->title}'.");
                        }
                    }
                });
            });

        NdlaArticleImportStatus::logDebug(14, "Norgesfilm: Population end.");
    }
}
