<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Cerpus\EdlibResourceKit\Lti\ContentItem\ContentItems;
use Cerpus\EdlibResourceKit\Lti\ContentItem\Mapper\ContentItemsMapperInterface;
use Cerpus\EdlibResourceKit\Lti\ContentItem\Serializer\ContentItemsSerializerInterface;
use Illuminate\Foundation\Http\FormRequest;
use function str_replace;

final class StoreContentRequest extends FormRequest
{
    public function __construct(
        private readonly ContentItemsMapperInterface $mapper,
        private readonly ContentItemsSerializerInterface $serializer,
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null,
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    protected function prepareForValidation(): void
    {
        $value = $this->input('content_items');

        if (!is_string($value)) {
            return;
        }

        // normalize content items
        $this->merge([
            'content_items' => $this->serializer->serialize($this->mapper->map($value)),
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
