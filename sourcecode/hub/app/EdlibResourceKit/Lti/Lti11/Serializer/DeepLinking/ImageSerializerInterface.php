<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\Image;

interface ImageSerializerInterface
{
    /**
     * @return array<mixed>
     *     The compact form JSON-LD representation of an image
     */
    public function serialize(Image $image): array;
}
