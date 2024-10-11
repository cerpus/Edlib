<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

/**
 * @deprecated This exists for compatibility with old integrations. New
 *     integrations with Edlib should not use this.
 */
final readonly class PublishController
{
    public function __invoke(string|null $id = null): void
    {
        // This currently does nothing, just like the endpoint this replaces.
    }
}
