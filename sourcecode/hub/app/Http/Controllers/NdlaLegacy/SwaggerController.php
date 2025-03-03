<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function resource_path;

/**
 * @deprecated This exists for compatibility with old integrations. New
 *     integrations with Edlib should not use this.
 */
final readonly class SwaggerController
{
    public function swagger(): Response
    {
        return response()->view('ndla-legacy.swagger');
    }

    public function redirect(): RedirectResponse
    {
        return redirect()->route('ndla-legacy.swagger', status: Response::HTTP_PERMANENTLY_REDIRECT);
    }

    public function schema(): BinaryFileResponse
    {
        return response()->file(resource_path('schema/ndla-openapi.json'), [
            'Content-Type' => 'application/json',
        ]);
    }
}
