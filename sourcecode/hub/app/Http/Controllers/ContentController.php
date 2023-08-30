<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContentRequest;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\StoreLtiResourceRequest;
use App\Lti\LtiLaunchBuilder;
use App\Models\Content;
use App\Models\ContentUserRole;
use App\Models\ContentVersion;
use App\Models\LtiResource;
use App\Models\LtiTool;
use App\Models\LtiVersion;
use Cerpus\EdlibResourceKit\Lti\ContentItem\Mapper\ContentItemsMapperInterface;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use function app;
use function assert;
use function is_string;
use function json_encode;
use function to_route;
use function view;

use const JSON_THROW_ON_ERROR;

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

        $user = $this->getUser();
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
        assert($credentials instanceof Credentials);

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

    public function addLtiResource(): View
    {
        return view('content.lti-add');
    }

    public function storeLtiResource(StoreLtiResourceRequest $request): RedirectResponse
    {
        $contentId = DB::transaction(function () use ($request) {
            $ltiTool = new LtiTool();
            $ltiTool->name = base64_encode(random_bytes(48));
            $ltiTool->consumer_key = $request->validated('consumer_key');
            $ltiTool->consumer_secret = $request->validated('consumer_secret');
            $ltiTool->lti_version = LtiVersion::Lti1_1;
            $ltiTool->creator_launch_url = 'https://none.example/'; // TODO
            $ltiTool->save();

            $ltiResource = new LtiResource();
            $ltiResource->lti_tool_id = $ltiTool->id;
            $ltiResource->title = $request->validated('title');
            $ltiResource->view_launch_url = $request->validated('launch_url');
            $ltiResource->edit_launch_url = 'https://none.example/'; // TODO
            $ltiResource->save();

            $content = new Content();
            $content->save();

            $contentVersion = new ContentVersion();
            $contentVersion->content_id = $content->id;
            $contentVersion->lti_resource_id = $ltiResource->id;
            $contentVersion->published = true;
            $contentVersion->save();

            return $content->id;
        });

        return to_route('content.preview', [$contentId]);
    }

    public function copy(Content $content): RedirectResponse
    {
        $user = $this->getUser();
        $copy = $content->createCopyBelongingTo($user);

        return to_route('content.index', [$copy->id]);
    }

    public function edit(Content $content, LtiLaunchBuilder $builder): View
    {
        $version = $content->latestPublishedVersion()->firstOrFail();

        $credentials = $version->resource?->tool?->getOauth1Credentials();
        assert($credentials instanceof Credentials);

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
        $item = $mapper->map(json_encode(
            $request->input('content_items'),
            flags: JSON_THROW_ON_ERROR,
        ))[0];

        $tool = LtiTool::where('consumer_key', $request->session()->get('lti.oauth_consumer_key'))
            ->firstOrFail();

        /*$content = */DB::transaction(function () use ($item, $tool) {
            $title = $item->getTitle() ?? throw new Exception('Missing title');
            $url = $item->getUrl() ?? throw new Exception('Missing URL');

            $resource = new LtiResource();
            $resource->title = $title;
            $resource->lti_tool_id = $tool->id;
            $resource->view_launch_url = $url;
            $resource->edit_launch_url = 'idk'; // TODO: figure this out
            $resource->save();

            $content = new Content();
            $content->save();

            $contentVersion = new ContentVersion();
            $contentVersion->lti_resource_id = $resource->id;
            $contentVersion->published = true;

            $content->users()->save($this->getUser(), [
                'role' => ContentUserRole::Owner,
            ]);

            $content->versions()->save($contentVersion);

            return $content;
        });

        return view('lti.close-edlib');
    }

    public function sitemap(): Response
    {
        $document = Content::generateSiteMap();

        return new Response($document->saveXML(), headers: [
            'Content-Type' => 'application/xml',
        ]);
    }
}
