<?php

namespace App\Transformers\Serializers;

use League\Fractal\Serializer\ArraySerializer as BaseArraySerializer;

class ArraySerializer extends BaseArraySerializer
{
    public function collection($resourceKey, array $data = null): array
    {
        return $data;
    }

    public function item($resourceKey, array $data = null): array
    {
        return ($data === null ? $this->null() : $data);
    }

    public function null(): ?array
    {
        return [];
    }
}
