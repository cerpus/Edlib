<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\H5PContent;
use App\Rules\LicenseContent;
use Illuminate\Foundation\Http\FormRequest;

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

        if ($this->has('isShared')) {
            $this->merge([
                'isShared' => $this->boolean('isShared'),
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
            'isShared' => ['sometimes', 'boolean'],
            'license' => [
                'required_if_accepted:isShared',
                config('app.enable_licensing') ? 'string' : 'nullable',
                new LicenseContent(),
            ],
        ];
    }
}
