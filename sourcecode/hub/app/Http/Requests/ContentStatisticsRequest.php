<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ContentViewSource;
use App\Enums\DateRangeResolution;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class ContentStatisticsRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'start' => 'filled|required_with:end|integer|min:1',
            'end' => 'filled|required_with:start|integer|min:1',
        ];
    }

    public function getStartDate(): CarbonImmutable|null
    {
        return $this->has('start') ? CarbonImmutable::createFromTimestampMsUTC($this->get('start')) : null;
    }

    public function getEndDate(): CarbonImmutable|null
    {
        return $this->has('end') ? CarbonImmutable::createFromTimestampMsUTC($this->get('end')) : null;
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
                ],
            ],
        };
    }
}
