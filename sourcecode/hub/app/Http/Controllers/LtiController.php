<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Lti\LtiLaunch;
use App\Models\Content;
use App\Models\ContentViewSource;
use App\Models\LtiPlatform;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function to_route;

final readonly class LtiController
{
    /**
     * The endpoint that will be loaded in LTI iframes.
     *
     * By having the iframe load its own script performing the LTI launch,
     * instead of submitting a form with the iframe as the target (as suggested
     * in the LTI spec), navigation with browser buttons doesn't break.
     *
     * The launch data is encrypted to prevent PII being stored in web server
     * logs.
     */
    public function launch(Request $request, Encrypter $encrypter): Response
    {
        $launch = $encrypter->decrypt($request->input('launch'));
        assert($launch instanceof LtiLaunch);

        return response()->view('lti.redirect', [
            'url' => $launch->getRequest()->getUrl(),
            'method' => $launch->getRequest()->getMethod(),
            'parameters' => $launch->getRequest()->toArray(),
        ]);
    }

    public function content(Content $content, Request $request): RedirectResponse
    {
        $key = $request->attributes->get('lti')['oauth_consumer_key'];
        $platform = LtiPlatform::where('id', $key)->firstOrFail();

        $content->trackView($request, ContentViewSource::LtiPlatform, $platform);

        return to_route('content.embed', [$content]);
    }

    public function select(): RedirectResponse
    {
        return to_route('content.index');
    }

    public function resizeTest(): Response
    {
        return response()->view('lti.resize-test');
    }
}
