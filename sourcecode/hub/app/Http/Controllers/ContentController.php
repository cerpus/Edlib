<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContentRequest;
use App\Lti\LtiLaunchBuilder;
use App\Lti\Oauth1\Oauth1Credentials;
use App\Models\Content;
use App\Models\LtiTool;
use Illuminate\Contracts\View\View;

use function assert;
use function is_string;
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

    public function show(Content $content, LtiLaunchBuilder $launchBuilder): View
    {
        $credentials = $content->latestVersion?->resource?->tool?->getOauth1Credentials();
        assert($credentials instanceof Oauth1Credentials);

        $launchUrl = $content->latestVersion?->resource?->view_launch_url;
        assert(is_string($launchUrl));

        $launch = $launchBuilder
            ->withWidth(640)
            ->withHeight(480)
            ->toPresentationLaunch($credentials, $launchUrl, $content->id);

        return view('content.show', [
            'content' => $content,
            'launch' => $launch,
        ]);
    }

    public function create(): View
    {
        $tools = LtiTool::all();

        return view('content.create', [
            'types' => $tools,
        ]);
    }

    public function edit(Content $content, LtiLaunchBuilder $builder): View
    {
        $credentials = $content->latestVersion?->resource?->tool?->getOauth1Credentials();
        assert($credentials instanceof Oauth1Credentials);

        $launchUrl = $content->latestVersion?->resource?->edit_launch_url;
        assert(is_string($launchUrl));

        $launch = $builder
            ->toPresentationLaunch($credentials, $launchUrl, $content->id);

        return view('content.edit', [
            'content' => $content,
            'launch' => $launch,
        ]);
    }

    public function launchCreator(LtiTool $tool, LtiLaunchBuilder $launchBuilder): View
    {
        $launch = $launchBuilder
            ->withWidth(640)
            ->withHeight(480)
            ->toItemSelectionLaunch(
                $tool->getOauth1Credentials(),
                $tool->creator_launch_url,
                '/lti/return' // TODO
            );

        return view('content.launch-creator', [
            'tool' => $tool,
            'launch' => $launch,
        ]);
    }
}
