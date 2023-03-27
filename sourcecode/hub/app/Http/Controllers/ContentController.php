<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContentRequest;
use App\Models\Content;
use App\Models\LtiTool;
use Illuminate\Contracts\View\View;

use function view;

class ContentController extends Controller
{
    public function index(IndexContentRequest $request): View
    {
        $query = $request->validated('q', '');

        if ($query !== '') {
            $contents = Content::search($query)->paginate();
        } else {
            $contents = Content::paginate();
        }

        return view('content.index', [
            'contents' => $contents,
            'query' => $query,
        ]);
    }

    public function create(): View
    {
        $tools = LtiTool::all();

        return view('content.create', [
            'types' => $tools,
        ]);
    }

    public function edit(Content $content): View
    {
        return view('content.edit', [
            'content' => $content,
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
