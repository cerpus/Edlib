<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class JsonSchema
{
    public function __construct(public string $id)
    {
    }
}
