<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Content;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Laravel\Scout\Builder;
use Override;

use function abort;
use function trans;

class ContentFilter extends FormRequest
{
    /** @var Builder<Content>  */
    private Builder $builder;
    private bool $forUser = false;
    private bool $languageChanged = false;
    private bool $queryChanged = false;
    private bool $typesChanged = false;

    #[Override] protected function failedValidation(Validator $validator): never
    {
        abort(404);
    }

    /**
     * @return array<string, mixed[]>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:300'],
            'language' => ['sometimes', 'string', 'max:100'],
            'sort' => ['sometimes', Rule::in('created', 'updated')],
            'type' => ['sometimes', 'array'],
        ];
    }

    public function setForUser(): void
    {
        $this->forUser = true;
    }

    public function isForUser(): bool
    {
        return $this->forUser;
    }

    public function hasQuery(): bool
    {
        return $this->safe()->has('q');
    }

    public function getQuery(): string
    {
        return $this->validated('q', '');
    }

    public function getLanguage(): string
    {
        return $this->validated('language', '');
    }

    /**
     * @return array<string, string>
     */
    public function getLanguageOptions(bool $withExpectedHits = false): array
    {
        $displayLocale = app()->getLocale();
        $fallBack = app()->getFallbackLocale();
        $options = collect($this->getLanguageOptionsWithHits());

        // Add the current selected value if not present, and if not present it has zero results
        $selectedOption = $this->getLanguage();
        if ($selectedOption !== '' && !$options->has($selectedOption)) {
            $options->put($selectedOption, 0);
        }

        return $options
            ->map(
                fn (int $value, string $key) =>
                $key === ''
                ? trans('messages.filter-language-all')
                : (locale_get_display_name($key, $displayLocale) ?: (locale_get_display_name($key, $fallBack) ?: $key))
            )
            ->when(
                $withExpectedHits,
                fn (Collection $items) =>
                $items->map(fn (string $value, string $key) => sprintf('%s (%d)', $value, $options[$key] ?? 0))
            )
            ->sort()
            ->toArray();
    }

    /**
     * @return array<string, int>
     */
    private function getLanguageOptionsWithHits(): array
    {
        $builder = clone($this->builder);
        unset($builder->wheres['language_iso_639_3']);

        $result = $builder
            ->options([
                'facets' => ['language_iso_639_3'],
            ])
            ->take(1)
            ->raw();

        $counts = $result['facetDistribution']['language_iso_639_3'] ?? [];
        $counts[''] = $result['totalHits'];

        return $counts;
    }

    /**
     * @return "updated"|"created"
     */
    public function getSortBy(): string
    {
        return $this->validated('sort', 'updated');
    }

    /**
     * @return array<string, string>
     */
    public function getSortOptions(): array
    {
        return [
            'updated' => trans('messages.edited'),
            'created' => trans('messages.created'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function getContentTypes(): array
    {
        return $this->validated('type', []);
    }

    /**
     * @return array<string, string>
     */
    public function getContentTypeOptions(bool $withExpectedHits = false): array
    {
        $options = collect($this->getContentTypeOptionsWithHits());
        $selectedOptions = $this->getContentTypes();

        // Add the current selected types if not present, not present means it has zero results
        foreach ($selectedOptions as $selected) {
            if (!$options->has($selected)) {
                $options->put($selected, 0);
            }
        }

        return $options
            ->map(fn (int $value, string $key) => $withExpectedHits ? sprintf('%s (%d)', $key, $value) : $key)
            ->sort()
            ->toArray();
    }

    /**
     * @return array<string, int>
     */
    private function getContentTypeOptionsWithHits(): array
    {
        $builder = clone($this->builder);
        unset($builder->whereIns['content_type']);

        $result = $builder
            ->options([
                'facets' => ['content_type'],
            ])
            ->take(1)
            ->raw();

        return $result['facetDistribution']['content_type'] ?? [];
    }

    /**
     * @return "grid"|"list"
     */
    public function getLayout(): string
    {
        return $this->session()->get('contentLayout', 'grid');
    }

    public function isTitlePreview(): bool
    {
        return $this->session()->has('lti');
    }

    /**
     * @param Builder<Content> $query
     * @return Builder<Content>
     */
    public function applyCriteria(Builder $query): Builder
    {
        $this->detectChanges();

        $query
            ->when(
                $this->getLanguage(),
                fn (Builder $query) => $query
                    ->where('language_iso_639_3', $this->getLanguage())
            )
            ->when(
                count($this->getContentTypes()) > 0,
                fn (Builder $query) => $query
                    ->whereIn('content_type', $this->getContentTypes())
            )
        ;

        $this->builder = clone($query);

        return $query->orderBy(match ($this->getSortBy()) {
            'created' => 'created_at',
            'updated' => $this->isForUser() ? 'updated_at' : 'published_at',
        }, 'desc');
    }

    /**
     * Number of active filters
     */
    public function activeCount(): int
    {
        return (empty($this->getLanguage()) ? 0 : 1) + (empty($this->getContentTypes()) ? 0 : 1);
    }

    /**
     * Could the updated filter values change the options for the content type filter?
     */
    public function shouldUpdateContentTypeOptions(): bool
    {
        return $this->languageChanged || $this->queryChanged;
    }

    /**
     * Could the updated filter values change the options for the language filter?
     */
    public function shouldUpdateLanguageOptions(): bool
    {
        return $this->typesChanged || $this->queryChanged;
    }

    /**
     * Figure out what filter values have changed and flash current input
     */
    private function detectChanges(): void
    {
        if ($this->getLanguage() !== old('language', '')) {
            $this->languageChanged = true;
        }

        if (json_encode($this->getContentTypes()) !== json_encode(old('type', []))) {
            $this->typesChanged = true;
        }

        if ($this->getQuery() !== old('q', '')) {
            $this->queryChanged = true;
        }

        $this->flash();
    }
}
