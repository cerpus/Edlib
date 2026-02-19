<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Edlib\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializer;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;

final readonly class EdlibContentItemsSerializer implements ContentItemsSerializerInterface
{
    private const CONTEXT = [
        'edlib' => 'https://spec.edlib.com/lti/vocab#',
        'xs' => 'http://www.w3.org/2001/XMLSchema#',
        'edlibVersionId' => [
            '@id' => 'edlib:edlibVersionId',
            '@type' => 'xs:normalizedString',
        ],
        'languageIso639_3' => [
            '@id' => 'edlib:languageIso639_3',
            '@type' => 'xs:normalizedString',
        ],
        'license' => [
            '@id' => 'edlib:license',
            '@type' => 'xs:normalizedString',
        ],
        'published' => [
            '@id' => 'edlib:published',
            '@type' => 'xs:boolean',
        ],
        'shared' => [
            '@id' => 'edlib:shared',
            '@type' => 'xs:boolean',
        ],
        'tag' => [
            '@id' => 'edlib:tag',
            '@type' => 'xs:normalizedString',
        ],
    ];

    public function __construct(
        private ContentItemsSerializerInterface $serializer = new ContentItemsSerializer(
            ltiLinkItemSerializer: new EdlibLtiLinkItemSerializer(),
        )
    ) {
    }

    public function serialize(array $items): array
    {
        $serialized = $this->serializer->serialize($items);

        if (!isset($serialized['@context'])) {
            return $serialized;
        }

        $context = $serialized['@context'];

        if (is_array($context) && array_is_list($context)) {
            $context = [...$context, self::CONTEXT];
        } else {
            $context = [$context, self::CONTEXT];
        }

        return [...$serialized, '@context' => $context];
    }
}
