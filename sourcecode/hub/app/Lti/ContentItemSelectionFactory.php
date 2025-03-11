<?php

declare(strict_types=1);

namespace App\Lti;

use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;

use const JSON_THROW_ON_ERROR;

/**
 * @todo move to edlib-resource-kit
 */
final readonly class ContentItemSelectionFactory
{
    public function __construct(
        private ContentItemsSerializerInterface $contentItemsSerializer,
        private SignerInterface $signer,
    ) {}

    /**
     * @param array<ContentItem> $items
     */
    public function createItemSelection(
        array $items,
        string $returnUrl,
        Credentials $credentials,
        string|null $data = null,
    ): Request {
        $serializedItems = json_encode(
            $this->contentItemsSerializer->serialize($items),
            flags: JSON_THROW_ON_ERROR,
        );

        return $this->signer->sign(new Request('POST', $returnUrl, [
            'content_items' => $serializedItems,
            'lti_message_type' => 'ContentItemSelection',
            'lti_version' => 'LTI-1p0',
            ...($data !== null ? ['data' => $data] : []),
        ]), $credentials);
    }
}
