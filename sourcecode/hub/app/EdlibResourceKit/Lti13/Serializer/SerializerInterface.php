<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13\Serializer;

interface SerializerInterface
{
    /**
     * Serialises LTI message objects. These may be either the root object or
     * any node of it.
     */
    public function serialize(object $message): array;
}
