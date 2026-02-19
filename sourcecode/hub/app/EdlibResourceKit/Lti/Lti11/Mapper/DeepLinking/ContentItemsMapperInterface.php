<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking;

use App\EdlibResourceKit\Lti\Exception\MappingException;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;

interface ContentItemsMapperInterface
{
    /**
     * @param array<mixed> $data
     * @return array<ContentItem>
     * @throws MappingException
     */
    public function map(array $data): array;
}
