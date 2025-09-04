<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function redirect;

final readonly class EdlibLegacyController
{
    public function redirectFromEdlib2Id(Content $content): RedirectResponse
    {
        return redirect()->route('content.embed', [$content]);
    }

    public function redirectLtiLaunch(Content $edlib2UsageContent): View
    {
        $version = $edlib2UsageContent->latestPublishedVersion ?? throw new NotFoundHttpException();
        $ltiRequest = $version->toLtiLaunch()->getRequest();

        return view('lti.redirect', [
            'url' => $ltiRequest->getUrl(),
            'method' => $ltiRequest->getMethod(),
            'parameters' => $ltiRequest->toArray(),
        ]);
    }
}
