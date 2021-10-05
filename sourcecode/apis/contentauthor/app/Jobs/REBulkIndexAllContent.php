<?php

namespace App\Jobs;

use App\Article;
use App\H5PContent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class REBulkIndexAllContent implements ShouldQueue
{
    public $timeout = 3600;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        Article::listedOnMarketplace()
            ->chunk(100, function ($articles) {
                $articles->each(function ($article) {
                    REUpdateOrCreate::dispatch($article)
                        ->onQueue("re-content-bulk");
                });
            });

        H5PContent::listedOnMarketplace()
            ->chunk(100, function ($articles) {
                $articles->each(function ($article) {
                    REUpdateOrCreate::dispatch($article)
                        ->onQueue("re-content-bulk");
                });
            });

    }
}
