<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final readonly class Claim
{
    public function __construct(
        public string|null $name = null,
    ) {
    }
}
