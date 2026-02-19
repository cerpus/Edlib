<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use App\EdlibResourceKit\Lti\Message\DeepLinking\FileItem;
use App\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;
use function array_map;

final readonly class ContentItemsSerializer implements ContentItemsSerializerInterface
{
    public function __construct(
        private ContentItemSerializerInterface $contentItemSerializer = new ContentItemSerializer(),
        private FileItemSerializerInterface $fileItemSerializer = new FileItemSerializer(),
        private LtiLinkItemSerializerInterface $ltiLinkItemSerializer = new LtiLinkItemSerializer(),
    ) {
    }

    public function serialize(array $items): array
    {
        return [
            '@context' => DeepLinkingProps::JSONLD_VOCAB,
            '@graph' => array_map(function (ContentItem $item) {
                if ($item instanceof FileItem) {
                    return $this->fileItemSerializer->serialize($item);
                }

                if ($item instanceof LtiLinkItem) {
                    return $this->ltiLinkItemSerializer->serialize($item);
                }

                return $this->contentItemSerializer->serialize($item);
            }, $items)
        ];
    }
}
