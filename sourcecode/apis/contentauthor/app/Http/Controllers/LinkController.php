<?php

namespace App\Http\Controllers;

use App\Link;
use App\Lti\Lti;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function __construct(private readonly Lti $lti) {}

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id): View
    {
        $customCSS = $this->lti->getRequest($request)?->getLaunchPresentationCssUrl();
        $link = Link::findOrFail($id);

        $metadata = !is_null($link->metadata) ? json_decode($link->metadata) : null;

        return view('link.show')->with(compact('link', 'customCSS', 'metadata'));
    }
}
