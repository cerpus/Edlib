<?php

declare(strict_types=1);

namespace App\Lti\ContentItem\Serializer;

use App\Lti\ContentItem\Image;

interface ImageSerializerInterface
{
    /**
     * @return array<mixed>
     */
    public function serialize(Image $image): array;
}
