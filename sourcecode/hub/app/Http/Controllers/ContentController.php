<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContentRequest;
use App\Http\Requests\StoreContentRequest;
use App\Lti\LtiLaunchBuilder;
use App\Lti\Oauth1\Oauth1Credentials;
use App\Models\Content;
use App\Models\ContentUserRole;
use App\Models\ContentVersion;
use App\Models\LtiResource;
use App\Models\LtiTool;
use App\Models\User;
use Cerpus\EdlibResourceKit\Lti\ContentItem\Mapper\ContentItemsMapperInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

use function app;
use function assert;
use function is_string;
use function json_encode;
use function to_route;
use function view;

class ContentController extends Controller
{
    public function index(IndexContentRequest $request): View
    {
        $query = $request->validated('q', '');
        assert(is_string($query));

        $contents = Content::findShared($query);

        return view('content.index', [
            'contents' => $contents->paginate(),
            'query' => $query,
        ]);
    }

    public function mine(IndexContentRequest $request): View
    {
        $query = $request->validated('q', '');
        assert(is_string($query));

        $user = auth()->user();
        assert($user instanceof User);

        $contents = Content::findForUser($user, $query);

        return view('content.mine', [
            'contents' => $contents->paginate(),
            'query' => $query,
        ]);
    }

    public function show(Content $content, LtiLaunchBuilder $launchBuilder): View
    {
        $version = $content->latestPublishedVersion()->firstOrFail();

        $credentials = $version->resource?->tool?->getOauth1Credentials();
        assert($credentials instanceof Oauth1Credentials);

        $launchUrl = $version->resource?->view_launch_url;
        assert(is_string($launchUrl));

        $launch = $launchBuilder
            ->withWidth(640)
            ->withHeight(480)
            ->withClaim('launch_presentation_locale', app()->getLocale())
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

    public function copy(Content $content): RedirectResponse
    {
        $user = auth()->user();
        assert($user instanceof User);

        $copy = $content->createCopyBelongingTo($user);

        return to_route('content.index', [$copy->id]);
    }

    public function edit(Content $content, LtiLaunchBuilder $builder): View
    {
        $version = $content->latestPublishedVersion()->firstOrFail();

        $credentials = $version->resource?->tool?->getOauth1Credentials();
        assert($credentials instanceof Oauth1Credentials);

        $launchUrl = $version->resource?->edit_launch_url;
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
            ->withClaim('launch_presentation_locale', app()->getLocale())
            ->toItemSelectionLaunch(
                $tool->getOauth1Credentials(),
                $tool->creator_launch_url,
                route('content.store'),
            );

        return view('content.launch-creator', [
            'tool' => $tool,
            'launch' => $launch,
        ]);
    }

    public function store(
        StoreContentRequest $request,
        ContentItemsMapperInterface $mapper,
    ): View {
        $item = $mapper->map(json_encode($request->input('content_items')))[0];

        $tool = LtiTool::where('consumer_key', $request->session()->get('lti.oauth_consumer_key'))
            ->firstOrFail();

        $content = DB::transaction(function () use ($item, $request, $tool) {
            $resource = new LtiResource();
            $resource->title = $item->getTitle();
            $resource->lti_tool_id = $tool->id;
            $resource->view_launch_url = $item->getUrl();
            $resource->edit_launch_url = 'idk'; // TODO: figure this out
            $resource->save();

            $content = new Content();
            $content->save();

            $contentVersion = new ContentVersion();
            $contentVersion->lti_resource_id = $resource->id;
            $contentVersion->published = true;

            $content->users()->save(auth()->user(), [
                'role' => ContentUserRole::Owner,
            ]);

            $content->versions()->save($contentVersion);

            return $content;
        });

        return view('lti.close-edlib');
    }
}
