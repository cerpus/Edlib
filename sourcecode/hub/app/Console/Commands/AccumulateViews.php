<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentView;
use App\Models\ContentViewsAccumulated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

final class AccumulateViews extends Command
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

        $statement = DB::getPdo()->prepare(<<<'EOSQL'
        SELECT
            content_id,
            source,
            lti_platform_id,
            (created_at AT TIME ZONE 'UTC')::DATE AS date,
            EXTRACT(hour FROM created_at AT TIME ZONE 'UTC') AS hour,
            COUNT(*) AS count
        FROM content_views
        WHERE created_at < :cutoff
        GROUP BY content_id, source, lti_platform_id, date, hour
        ORDER BY date, hour
        EOSQL);
        $statement->bindValue(':cutoff', $cutoff->format('c'));
        $statement->execute();

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $criteria = [
                'content_id' => $row['content_id'],
                'source' => $row['source'],
                'lti_platform_id' => $row['lti_platform_id'],
                'date' => $row['date'],
                'hour' => $row['hour'],
            ];
            $beforeCount += $row['count'];

            $accumulated = ContentViewsAccumulated::where($criteria)->firstOr(
                fn() => ContentViewsAccumulated::forceCreate($criteria),
            );
            $accumulated->view_count += $row['count'];
            $accumulated->save();

            ContentView::where('created_at', '<', $cutoff)->delete();

            $afterCount++;
        }

        $this->info("Accumulated $beforeCount views into $afterCount rows");

        return self::SUCCESS;
    }
}
