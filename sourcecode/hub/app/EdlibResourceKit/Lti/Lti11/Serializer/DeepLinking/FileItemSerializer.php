<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Message\DeepLinking\FileItem;

final readonly class FileItemSerializer implements FileItemSerializerInterface
{
    public function __construct(
        private ContentItemSerializerInterface $serializer = new ContentItemSerializer(),
    ) {
    }

    public function serialize(FileItem $item): array
    {
        $serialized = [
            ...$this->serializer->serialize($item),
            '@type' => 'FileItem',
        ];

        if ($item->getCopyAdvice() !== null) {
            $serialized[Prop::COPY_ADVICE] = $item->getCopyAdvice();
        }

        if ($item->getExpiresAt() !== null) {
            $serialized[Prop::EXPIRES_AT] = $item->getExpiresAt()->format('c');
        }

        return $serialized;
    }
}
