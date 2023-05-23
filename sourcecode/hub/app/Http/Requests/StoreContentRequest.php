<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Cerpus\EdlibResourceKit\Lti\ContentItem\ContentItems;
use Cerpus\EdlibResourceKit\Lti\ContentItem\Mapper\ContentItemsMapperInterface;
use Cerpus\EdlibResourceKit\Lti\ContentItem\Serializer\ContentItemsSerializerInterface;
use Illuminate\Foundation\Http\FormRequest;

use function app;
use function str_replace;

final class StoreContentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $serializer = app()->make(ContentItemsSerializerInterface::class);
        $mapper = app()->make(ContentItemsMapperInterface::class);

        $value = $this->input('content_items');

        if (!is_string($value)) {
            return;
        }

        // normalize content items
        $this->merge([
            'content_items' => $serializer->serialize($mapper->map($value)),
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        $propTitle = str_replace('.', '\.', ContentItems::PROP_TITLE);
        $propUrl = str_replace('.', '\.', ContentItems::PROP_URL);

        return [
            "content_items.@graph.0.@type" => ['required', 'in:LtiLinkItem'],
            "content_items.@graph.0.$propTitle" => ['required', 'string'],
            "content_items.@graph.0.$propUrl" => ['required', 'url'],
        ];
    }
}
