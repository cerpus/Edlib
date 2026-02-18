<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Cerpus\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;
use Illuminate\Foundation\Http\FormRequest;

use function app;

final class DeepLinkingReturnRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $serializer = app()->make(ContentItemsSerializerInterface::class);
        $mapper = app()->make(ContentItemsMapperInterface::class);

        $value = json_decode($this->input('content_items'), associative: true);

        if (is_array($value)) {
            // normalize content items
            $this->merge([
                'content_items' => $serializer->serialize($mapper->map($value)),
            ]);
        }
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'content_items.@graph.0.@type' => ['required', 'in:LtiLinkItem'],
            'content_items.@graph.0.title' => ['required', 'string'],
            'content_items.@graph.0.url' => ['required', 'url'],
            'content_items.@graph.0.license' => ['sometimes', 'required', 'string'],
            'content_items.@graph.0.language_iso_639_3' => ['sometimes', 'required', 'string', 'size:3'],
        ];
    }
}
