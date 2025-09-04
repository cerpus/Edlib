<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Models\Content;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function is_string;
use function redirect;

/**
 * @deprecated This exists for compatibility with old integrations. New
 *     integrations with Edlib should not use this.
 */
final readonly class ViewResourceController
{
    public function __invoke(Content $edlib2UsageContent, Request $request): RedirectResponse
    {
        $query = [];

        $locale = $request->input('locale');
        if (is_string($locale)) {
            $query['locale'] = $locale;
        }

        return redirect()->route('content.embed', [
            $edlib2UsageContent,
            ...$query,
        ]);
    }
}
