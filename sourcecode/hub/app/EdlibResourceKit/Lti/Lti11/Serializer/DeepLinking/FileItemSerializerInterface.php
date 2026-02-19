<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\FileItem;

interface FileItemSerializerInterface
{
    /**
     * @return array<mixed>
     *     The compact form JSON-LD representation of an LTI file item
     */
    public function serialize(FileItem $item): array;
}
