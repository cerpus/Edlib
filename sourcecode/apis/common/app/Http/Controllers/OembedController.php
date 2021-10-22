<?php

namespace App\Http\Controllers;

use App\Models\OembedContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OembedController extends Controller
{
    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function preStartContentExplorer(Request $request): \Illuminate\Http\RedirectResponse
    {
        $oembedContext = OembedContext::create([
            'jwt' => $request->get('internalToken')
        ]);

        return redirect()->action(
            [OembedController::class, 'startContentExplorer'], ['oembedContext' => $oembedContext]
        );
    }

    public function startContentExplorer(Request $request, OembedContext $oembedContext): View
    {
        return view('oembed.startContentExplorer', ['edlibContentExplorerIframeUrl' => $request->getSchemeAndHttpHost() . '/iframe/content-explorer']);
    }
}
