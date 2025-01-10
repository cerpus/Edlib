<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Exception;
use App\Article;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class AdminArticleController extends Controller
{
    public const chunkSize = 30;
    public const logFile = 'articleMaxScore.log';

    private Filesystem $log;

    public function __construct()
    {
        $this->log = Storage::disk('storageLogs');
    }

    public function index()
    {
        $numNoMaxScore = Article::noMaxScore()->ofBulkCalculated(Article::BULK_UNTOUCHED)->count();
        $updateRoute = route('admin.article.maxscore.update');
        $downloadRoute = route('admin.article.maxscore.download');
        $failedRoute = route('admin.article.maxscore.failed');

        return view('admin.articles.index', compact('numNoMaxScore', 'updateRoute', 'downloadRoute', 'failedRoute'));
    }

    public function updateMaxScore()
    {
        /** @var Builder $builder */
        $builder = Article::noMaxScore()
            ->ofBulkCalculated(Article::BULK_UNTOUCHED);
        $batch = collect();

        $targets = $builder->take(self::chunkSize);
        $currentTargets = $targets->get();
        $targets->update(['bulk_calculated' => Article::BULK_PROGRESS]);

        $currentTargets
            ->each(function ($article) use ($batch) {
                /** @var Article $article */
                try {
                    $status = [
                        'id' => $article->id,
                        'title' => $article->title,
                    ];
                    $article->max_score = $article->getMaxScoreHelper($article->content, true);
                    $article->bulk_calculated = Article::BULK_UPDATED;
                    $status['success'] = true;
                    $this->log("SUCCESS", $status);
                } catch (Exception $exception) {
                    $article->bulk_calculated = Article::BULK_FAILED;
                    $article->max_score = null;
                    $status['success'] = false;
                    $status['errorCode'] = $exception->getCode();
                    $status['errorMessage'] = $exception->getMessage();
                    $this->log("ERROR", $status);
                } finally {
                    $batch->push($status);
                    $article->save();
                }
            });
        return response()->json([
            'outstanding' => $builder->count(),
            'batch' => $batch,
        ]);
    }

    private function log($message, $context = null)
    {
        $lines = [
            '[' . Carbon::now()->format('Y-m-d H:i:s') . ']',
            $message,
        ];

        if (is_scalar($context)) {
            $lines[] = (string) $context;
        } else {
            if (is_array($context) && count($context) > 0) {
                $lines[] = json_encode($context);
            }
        }

        $this->log->append(self::logFile, implode(" ", $lines));
    }

    public function download()
    {
        $file = $this->log->path(self::logFile);
        return response()->download($file, self::logFile);
    }


    public function viewFailedCalculations()
    {
        $resources = Article::ofBulkCalculated(Article::BULK_FAILED)->get();

        return view('admin.articles.maxscore-failed-overview', compact('resources'));
    }
}
