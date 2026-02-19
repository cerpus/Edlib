<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking;

use App\EdlibResourceKit\Lti\Exception\MappingException;
use App\EdlibResourceKit\Lti\Message\DeepLinking\Image;

interface ImageMapperInterface
{
    /**
     * @param array<mixed> $data
     * @throws MappingException
     */
    public function map(array $data): Image|null;
}
