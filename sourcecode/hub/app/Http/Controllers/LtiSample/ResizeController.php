<?php

declare(strict_types=1);

namespace App\Http\Controllers\LtiSample;

use Symfony\Component\HttpFoundation\Response;

use function response;

final readonly class ResizeController
{
    public function __invoke(): Response
    {
        return response()->view('lti.samples.resize');
    }
}
