<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\H5PContent;
use App\Rules\LicenseContent;
use App\Rules\shareContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use function assert;

class H5PStorageRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('isPublished')) {
            $this->merge([
                'isPublished' => $this->boolean('isPublished'),
            ]);
        }
    }

    public function rules(): array
    {
        $content = $this->route()->parameter('h5p') ?? H5PContent::make();
        assert($content instanceof H5PContent);

        return [
            'title' => 'required|string|min:1|max:255',
            'libraryid' => 'nullable|sometimes|exists:h5p_libraries,id',
            'library' => 'required_without:libraryid|string',
            'parameters' => 'required|json',
            'language_iso_639_3' => 'nullable|string|min:3|max:3',
            'isNewLanguageVariant' => 'nullable|boolean',
            'isDraft' => 'required|boolean',
            'isPublished' => ['sometimes', 'boolean'],
            'share' => [
                'sometimes',
                new shareContent(),
            ],
            'license' => [
                Rule::requiredIf($this->input('share') === 'share'),
                config('app.enable_licensing') ? 'string' : 'nullable',
                new LicenseContent(),
            ],
        ];
    }
}
