<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Content;
use App\Models\ContentView;
use App\Models\ContentViewsAccumulated;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\DB;

final class AccumulateViews extends Command implements Isolatable
{
    protected $signature = <<<'EOSIGNATURE'
    edlib:accumulate-views
    {cutoff : The max timestamp (exclusive) of views to accumulate, in PHP's \DateTime format}
    EOSIGNATURE;

    protected $description = 'Accumulate individual views into grouped views for statistics';

    public function handle(): int
    {
        $cutoff = new \DateTimeImmutable($this->argument('cutoff'));
        $beforeCount = 0;
        $afterCount = 0;

        if (!now()->isAfter($cutoff)) {
            $this->fail('Cutoff must be in the past');
        }

        foreach (Content::getAccumulatableViews($cutoff) as $row) {
            $beforeCount += $row['count'];

            DB::transaction(function () use ($row, $cutoff) {
                $criteria = [
                    'content_id' => $row['content_id'],
                    'source' => $row['source'],
                    'lti_platform_id' => $row['lti_platform_id'],
                    'date' => $row['date'],
                    'hour' => $row['hour'],
                ];

                $accumulated = ContentViewsAccumulated::where($criteria)
                    ->firstOr(fn() => ContentViewsAccumulated::forceCreate($criteria));
                $accumulated->view_count += $row['count'];
                $accumulated->save();

                ContentView::where('created_at', '<', $cutoff)->delete();
                Content::where('id', $row['content_id'])->touch();
            });

            $afterCount++;
        }

        $this->info("Accumulated $beforeCount views into $afterCount rows");

        return self::SUCCESS;
    }
}
