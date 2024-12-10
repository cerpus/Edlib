<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ContentViewSource;
use App\Enums\DateRangeResolution;
use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContentStatisticsRequest extends FormRequest
{
    private DateRangeResolution $resolution = DateRangeResolution::Year;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'start' => 'filled|required_with:end|integer|min:1|max:' . PHP_INT_MAX,
            'end' => 'filled|required_with:start|integer|min:1|max:' . PHP_INT_MAX,
        ];
    }

    public function getStartDate(): Carbon|null
    {
        return $this->has('start') ? Carbon::createFromTimestampMsUTC($this->get('start')) : null;
    }

    public function getEndDate(): Carbon|null
    {
        return $this->has('end') ? Carbon::createFromTimestampMsUTC($this->get('end')) : null;
    }

    public function rangeResolution(Carbon $start, Carbon $end): DateRangeResolution
    {
        if ($start->diffInUTCMonths($end) < 3) {
            return DateRangeResolution::Day;
        } elseif ($start->diffInUTCYears($end) < 3) {
            return DateRangeResolution::Month;
        }

        return DateRangeResolution::Year;
    }

    /**
     * @return Collection<int, mixed>
     */
    public function getData(Content $content, Carbon|null $start = null, Carbon|null $end = null, DateRangeResolution|null $resolution = null): Collection
    {
        if (!$start || !$end) {
            $start = $this->getStartDate();
            $end = $this->getEndDate();

            if (!$start || !$end) {
                assert($content->created_at instanceof Carbon);
                $start = $content->created_at;
                $end = Carbon::now();
            }
        }

        $this->resolution = $resolution ?? $this->rangeResolution($start, $end);

        switch ($this->resolution) {
            case DateRangeResolution::Year:
                $start = $start->startOfYear();
                $end = $end->endOfYear();
                break;
            case DateRangeResolution::Month:
                $start = $start->startOfMonth();
                $end = $end->endOfMonth();
                break;
            case DateRangeResolution::Day:
                $start = $start->startOfDay();
                $end = $end->endOfDay();
                break;
        }

        return DB::table('content_views')
            ->select([
                DB::raw("date_trunc('{$this->resolution->value}', created_at)::DATE point"),
                DB::raw('count(*) views'),
                'source',
            ])
            ->where('content_id', '=', $content->id)
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy(['point', 'source'])
            ->orderBy('point', 'ASC')
            ->get()
            ->groupBy(['point'])
            ->map(function (Collection $items, $key) {
                $out = $this->dataGroups()
                    ->mapWithKeys(function (string $value) {
                        return [$value => 0];
                    })
                    ->put('point', $key);

                $items->each(function ($item) use (&$out) {
                    /** @var object{'source': string, 'views': int} $item */
                    $out[$item->source] = $item->views;
                    $out['total'] += $item->views;
                });

                return $out;
            })
            ->values();
    }

    /**
     * @return Collection<array-key, string>
     */
    public function dataGroups(): Collection
    {
        return collect([
            ContentViewSource::Detail->value,
            ContentViewSource::Embed->value,
            ContentViewSource::LtiPlatform->value,
            ContentViewSource::Share->value,
            'total',
        ]);
    }

    /**
     * @return array<string, string|array<string, string>>
     */
    public function getDateFormatsForResolution(DateRangeResolution|null $resolution = null): array
    {
        return match ($resolution ?? $this->resolution) {
            DateRangeResolution::Day => [
                'resolution' => DateRangeResolution::Day->value,
                'dataFormat' => '%Y-%m-%d',
                'tickFormat' => 'short',
                'tooltipFormat' => [
                    'year' => 'numeric',
                    'month' => 'long',
                    'day' => 'numeric',
                    'weekday' => 'long',
                    'timeZone' => 'UTC',
                ],
            ],
            DateRangeResolution::Month => [
                'resolution' => DateRangeResolution::Month->value,
                'dataFormat' => '%Y-%m',
                'tickFormat' => [
                    'year' => 'numeric',
                    'month' => 'long',
                ],
                'tooltipFormat' => [
                    'year' => 'numeric',
                    'month' => 'long',
                    'timeZone' => 'UTC',
                ],
            ],
            default => [
                'resolution' => DateRangeResolution::Year->value,
                'dataFormat' => '%Y',
                'tickFormat' => [
                    'year' => 'numeric',
                ],
                'tooltipFormat' => [
                    'year' => 'numeric',
                    'timeZone' => 'UTC',
                ],
            ],
        };
    }
}
