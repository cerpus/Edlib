<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\LtiTool;
use Illuminate\Contracts\View\View;

use function view;

class ContentController extends Controller
{
    public function index(): View
    {
        return view('content.index', [
            'contents' => Content::paginate(25),
        ]);
    }

    public function create(): View
    {
        $tools = LtiTool::all();

        return view('content.create', [
            'types' => $tools,
        ]);
    }

    public function launchCreator(LtiTool $tool): View
    {
        return view('content.launch-creator', [
            'tool' => $tool,
        ]);
    }

    public function preview(Content $content): View
    {
        $launchUrl = $content->latestVersion?->resource?->view_launch_url;
        assert(is_string($launchUrl));

        return view('content.preview', [
            'content' => $content,
            'launch_url' => $launchUrl,
        ]);
    }
}
