<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use App\EdlibResourceKit\Lti\Exception\MappingException;
use function array_is_list;
use function is_array;

final readonly class ContentItemsMapper implements ContentItemsMapperInterface
{
    public function __construct(
        private ContentItemMapperInterface $contentItemMapper = new ContentItemMapper(),
    ) {
    }

    /**
     * @return array<ContentItem>
     */
    public function map(array $data): array
    {
        if (!isset($data['@context'])) {
            throw new MappingException('Missing JSON-LD context');
        }

        if ($data['@context'] !== Prop::JSONLD_VOCAB && !(
            is_array($data['@context']) &&
            array_is_list($data['@context']) &&
            in_array(Prop::JSONLD_VOCAB, $data['@context'], true)
        )) {
            throw new MappingException('Invalid or unsupported JSON-LD context');
        }

        // JSON-LD compacting can result in items either being on the top level,
        // or being in the @graph array.
        $items = $data['@graph'] ?? [$data];

        if (!is_array($items)) {
            throw new MappingException('Invalid @graph');
        }

        return array_map($this->contentItemMapper->map(...), $items);
    }
}
