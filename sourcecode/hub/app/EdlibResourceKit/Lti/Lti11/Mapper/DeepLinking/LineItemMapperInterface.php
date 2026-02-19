<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\LineItem;

interface LineItemMapperInterface
{
    public function map(array $data): LineItem|null;
}
