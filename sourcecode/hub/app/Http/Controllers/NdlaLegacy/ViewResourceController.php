<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Models\Content;
use Illuminate\Http\RedirectResponse;

use function redirect;

/**
 * @deprecated This exists for compatibility with old integrations. New
 *     integrations with Edlib should not use this.
 */
final readonly class ViewResourceController
{
    public function __invoke(Content $edlib2UsageContent): RedirectResponse
    {
        return redirect()->route('content.embed', [$edlib2UsageContent]);
    }
}
