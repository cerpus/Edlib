<?php

declare(strict_types=1);

namespace App\Http\Controllers\LtiSample;

use App\Models\LtiPlatform;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function abort;
use function redirect;

/**
 * Test sending arbitrary payloads in LTI Deep-Linking responses.
 */
final readonly class DeepLinkController
{
    public function launch(): RedirectResponse
    {
        return redirect()->route('lti.samples.deep-link.form');
    }

    public function form(): View
    {
        return view('lti.samples.deep-link');
    }

    public function return(Request $request, SignerInterface $signer): View
    {
        $key = $request->session()->get('lti.oauth_consumer_key') ?? abort(400);
        $url = $request->session()->get('lti.content_item_return_url') ?? abort(400);

        $credentials = LtiPlatform::where('key', $key)
            ->firstOrFail()
            ->getOauth1Credentials();

        $data = $request->validate([
            'payload' => ['required', 'json'],
        ]);

        $oauthRequest = $signer->sign(new Oauth1Request('POST', $url, [
            'content_items' => $data['payload'],
            'lti_message_type' => 'ContentItemSelection',
            'lti_version' => 'LTI-1p0',
        ]), $credentials);

        return view('lti.redirect', [
            'url' => $oauthRequest->getUrl(),
            'method' => $oauthRequest->getMethod(),
            'parameters' => $oauthRequest->toArray(),
        ]);
    }
}
