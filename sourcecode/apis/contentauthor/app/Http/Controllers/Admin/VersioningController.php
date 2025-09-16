<?php

namespace App\Http\Controllers\Admin;

use App\Content;
use App\Http\Controllers\Controller;
use App\Libraries\Versioning\VersionableObject;
use Illuminate\Http\Request;

class VersioningController extends Controller
{
    public function index(Request $request)
    {
        $isContentVersioned = true;
        $versionData = collect();
        $contentId = $request->input('contentId');
        if ($contentId) {
            $content = Content::findContentById($contentId);
            if ($content instanceof VersionableObject) {
                $versionData = Content::collectVersionData($content);
            } else {
                $isContentVersioned = false;
            }
        }

        return view('admin.support.versioning')->with([
            'contentId' => $contentId,
            'versionData' => $versionData->toArray(),
            'isContentVersioned' => $isContentVersioned,
        ]);
    }
}
