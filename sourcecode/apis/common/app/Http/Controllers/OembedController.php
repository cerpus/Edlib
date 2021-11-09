<?php

namespace App\Http\Controllers;

use App\Models\OembedContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OembedController extends Controller
{
    public function preStartContentExplorer(Request $request): \Illuminate\Http\RedirectResponse
    {
        $oembedContext = OembedContext::create([
            'jwt' => $request->get('internalToken')
        ]);

        return redirect()->action(
            [OembedController::class, 'startContentExplorer'], ['oembedContext' => $oembedContext]
        );
    }

    public function startContentExplorer(OembedContext $oembedContext): View
    {
        $url = url('/iframe/content-explorer') . '?' . http_build_query(
                [
                    'jwt' => $oembedContext->jwt
                ]
            );

        return view('oembed.startContentExplorer', [
            'edlibContentExplorerIframeUrl' => $url,
            'returnUrl' => route('oembed.selectReturn')
        ]);
    }

    public function selectReturn(Request $request): View
    {
        $url = $request->get("url");

        return view('oembed.selectReturn', [
            'contentType' => $url,
            'h5pId' => $url,
            'embedId' => $url,
            'oembedUrl' => $url,
        ]);
    }
}
