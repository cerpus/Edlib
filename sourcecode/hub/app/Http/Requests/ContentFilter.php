<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ContentVersion;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laravel\Scout\Builder;
use Override;

use function abort;
use function trans;

class ContentFilter extends FormRequest
{
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
        ];
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
    public function getLanguageOptions(): array
    {
        return ContentVersion::getTranslatedUsedLocales($this->user());
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
            'updated' => trans('messages.sort-last-updated'),
            'created' => trans('messages.sort-last-created'),
        ];
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

    public function applyCriteria(Builder $query): Builder
    {
        return $query
            ->when(
                $this->getLanguage(),
                fn (Builder $query) => $query
                    ->where('language_iso_639_3', $this->getLanguage())
            )
            ->orderBy(match ($this->getSortBy()) {
                'created' => 'created_at',
                'updated' => 'updated_at',
            }, 'desc')
        ;
    }
}
