<?php

declare(strict_types=1);

namespace App\EdlibResource;

use Cerpus\EdlibResourceKit\Contract\EdlibResource;
use Cerpus\EdlibResourceKit\Serializer\ResourceSerializer as BaseResourceSerializer;

class ResourceSerializer extends BaseResourceSerializer
{
    public function serialize(EdlibResource $resource): array
    {
        $data = parent::serialize($resource);

        if ($resource instanceof CaEdlibResource) {
            $data['authorOverwrite'] = $resource->getAuthorOverwrite();
        }

        return $data;
    }
}
