<?php

namespace App\Jobs;

use App\Article;
use App\H5PContent;
use App\NdlaIdMapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class REBulkIndexAllNDLAArticlesContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $timeout = 1800;

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
        NdlaIdMapper::article()->cursor()
            ->each(function ($articleMap) {
                if ($article = Article::find($articleMap->ca_id)) {
                    REUpdateOrCreate::dispatch($article)
                        ->onQueue("re-content-bulk");
                }
            });

        NdlaIdMapper::H5P()->cursor()
            ->each(function ($h5pMap) {
                if ($h5p = H5PContent::find($h5pMap->ca_id)) {
                    REUpdateOrCreate::dispatch($h5p)
                        ->onQueue("re-content-bulk");
                }
            });

    }
}
