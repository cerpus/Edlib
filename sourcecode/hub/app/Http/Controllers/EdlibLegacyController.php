<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\RedirectResponse;
use function redirect;
use function route;

final readonly class EdlibLegacyController
{
    public function redirectFromEdlib2Id(Content $edlib2Content): RedirectResponse
    {
        return redirect()->route('content.embed', [$edlib2Content]);
    }

    public function redirectLtiLaunch(Content $edlib2UsageContent): RedirectResponse
    {
        return new RedirectResponse(
            route('content.embed', [$edlib2UsageContent]),
            status: 307,
        );
    }
}
