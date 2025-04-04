<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\ContentViewSource;
use App\Enums\DateRangeResolution;

use function array_map;
use function count;
use function sprintf;

class ContentStats
{
    /** @var array<int, array<value-of<ContentViewSource>, int>> */
    private array $viewsByYearAndSource = [];

    /** @var array<string, array<value-of<ContentViewSource>, int>> */
    private array $viewsByMonthAndSource = [];

    /** @var array<string, array<value-of<ContentViewSource>, int>> */
    private array $viewsByDayAndSource = [];

    public function __construct() {}

    public function addStat(ContentViewSource $source, int $views, int $year, int $month, int $day): void
    {
        $this->viewsByYearAndSource[$year] ??= [];
        $this->viewsByYearAndSource[$year][$source->value] ??= 0;
        $this->viewsByYearAndSource[$year][$source->value] += $views;

        $key = sprintf('%04d-%02d', $year, $month);
        $this->viewsByMonthAndSource[$key] ??= [];
        $this->viewsByMonthAndSource[$key][$source->value] ??= 0;
        $this->viewsByMonthAndSource[$key][$source->value] += $views;

        $key = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $this->viewsByDayAndSource[$key] ??= [];
        $this->viewsByDayAndSource[$key][$source->value] ??= 0;
        $this->viewsByDayAndSource[$key][$source->value] += $views;
    }

    /**
     * @return array<int|string, array<value-of<ContentViewSource>, int>|array{point: string, total: int}>
     *     View stats, mapped by data point (i.e. dates).
     */
    public function getData(DateRangeResolution|null $resolution = null): array
    {
        $resolution ??= $this->inferResolution();

        $views = match ($resolution) {
            DateRangeResolution::Year => $this->viewsByYearAndSource,
            DateRangeResolution::Month => $this->viewsByMonthAndSource,
            DateRangeResolution::Day => $this->viewsByDayAndSource,
        };

        return array_map(function (array $viewsByPoint, int|string $point): array {
            $total = 0;
            $data = [];

            foreach (ContentViewSource::cases() as $source) {
                $total += $data[$source->value] = $viewsByPoint[$source->value] ?? 0;
            }
            $data['point'] = (string) $point;
            $data['total'] = $total;

            return $data;
        }, $views, array_keys($views));
    }

    /**
     * Use the view data to infer an appropriate resolution.
     */
    public function inferResolution(): DateRangeResolution
    {
        if (count($this->viewsByMonthAndSource) < 3) {
            return DateRangeResolution::Day;
        }

        if (count($this->viewsByYearAndSource) < 3) {
            return DateRangeResolution::Month;
        }

        return DateRangeResolution::Year;
    }
}
