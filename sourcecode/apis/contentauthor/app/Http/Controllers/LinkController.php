<?php

namespace App\Http\Controllers;

use App\ACL\ArticleAccess;
use App\Http\Libraries\LtiTrait;
use App\Link;
use App\Lti\Lti;
use Illuminate\View\View;

class LinkController extends Controller
{
    use LtiTrait;
    use ArticleAccess;

    public function __construct(private readonly Lti $lti)
    {
    }

    /**
     * Display the specified resource.
     */
    public function doShow($id, $context, $preview = false): View
    {
        $customCSS = $this->lti->getRequest(request())?->getLaunchPresentationCssUrl();
        $link = Link::findOrFail($id);
        if (!$link->canShow($preview)) {
            return view('layouts.draft-resource', [
                'styles' => !is_null($customCSS) ? [$customCSS] : [],
            ]);
        }

        $metadata = !is_null($link->metadata) ? json_decode($link->metadata) : null;

        return view('link.show')->with(compact('link', 'customCSS', 'metadata'));
    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }
}
