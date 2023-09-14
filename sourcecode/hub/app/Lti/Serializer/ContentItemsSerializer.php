<?php

declare(strict_types=1);

namespace App\Lti\Serializer;

use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;

use function array_is_list;
use function is_array;

/**
 * Add custom JSON-LD contexts for Edlib
 */
final readonly class ContentItemsSerializer implements ContentItemsSerializerInterface
{
    private const CONTEXT = [
        'edlib' => 'https://spec.edlib.com/lti/vocab#',
        'xs' => 'http://www.w3.org/2001/XMLSchema#',
        'languageIso639_3' => [
            '@id' => 'edlib:languageIso639_3',
            '@type' => 'xs:normalizedString',
        ],
        'license' => [
            '@id' => 'edlib:license',
            '@type' => 'xs:normalizedString',
        ],
    ];

    public function __construct(private ContentItemsSerializerInterface $serializer)
    {
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
