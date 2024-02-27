<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DeepLinkingReturnRequest;
use App\Lti\LtiLaunchBuilder;
use App\Models\Content;
use App\Models\ContentUserRole;
use App\Models\ContentVersion;
use App\Models\ContentViewSource;
use App\Models\LtiTool;
use App\Models\LtiToolEditMode;
use Cerpus\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use function assert;
use function is_string;
use function redirect;
use function to_route;
use function view;

class ContentController extends Controller
{
    public function index(): View
    {
        return view('content.index');
    }

    public function mine(): View
    {
        return view('content.mine');
    }

    public function details(Content $content, Request $request): View
    {
        $version = $content->latestPublishedVersion()->firstOrFail();
        $this->authorize('view', [$content, $version]);

        $content->trackView($request, ContentViewSource::Detail);

        return view('content.details', [
            'content' => $content,
            'version' => $version,
            'launch' => $version->toLtiLaunch(),
        ]);
    }

    public function version(Content $content, ContentVersion $version): View
    {
        return view('content.details', [
            'content' => $content,
            'version' => $version,
            'launch' => $version->toLtiLaunch(),
            'explicitVersion' => true,
        ]);
    }

    public function share(Content $content, Request $request): View
    {
        $content->trackView($request, ContentViewSource::Share);

        $launch = $content
            ->latestPublishedVersion()
            ->firstOrFail()
            ->toLtiLaunch();

        return view('content.share', [
            'content' => $content,
            'launch' => $launch,
        ]);
    }

    public function embed(Content $content): View
    {
        $launch = $content
            ->latestPublishedVersion()
            ->firstOrFail()
            ->toLtiLaunch();

        return view('content.embed', [
            'content' => $content,
            'version' => $content->latestPublishedVersion,
            'launch' => $launch,
        ]);
    }

    public function preview(Content $content, ContentVersion $version): View
    {
        return view('content.preview', [
            'launch' => $version->toLtiLaunch(),
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
        $user = $this->getUser();
        $copy = $content->createCopyBelongingTo($user);

        return to_route('content.index', [$copy->id]);
    }

    public function edit(
        Content $content,
        ContentVersion $version,
        LtiLaunchBuilder $builder,
    ): View {
        $tool = $version->tool ?? abort(404);

        $launchUrl = match ($tool->edit_mode) {
            LtiToolEditMode::Replace => $tool->creator_launch_url,
            LtiToolEditMode::DeepLinkingRequestToContentUrl => $version->lti_launch_url,
        };
        assert(is_string($launchUrl));

        $launch = $builder->toItemSelectionLaunch(
            $tool,
            $launchUrl,
            route('content.lti-update', [$tool, $content]),
        );

        return view('content.edit', [
            'content' => $content,
            'launch' => $launch,
        ]);
    }

    public function use(Content $content): View
    {
        $ltiRequest = $content->toItemSelectionRequest();

        return view('lti.redirect', [
            'url' => $ltiRequest->getUrl(),
            'method' => $ltiRequest->getMethod(),
            'parameters' => $ltiRequest->toArray(),
        ]);
    }

    public function launchCreator(LtiTool $tool, LtiLaunchBuilder $launchBuilder): View
    {
        $launch = $launchBuilder
            ->withWidth(640)
            ->withHeight(480)
            ->toItemSelectionLaunch(
                $tool,
                $tool->creator_launch_url,
                route('content.lti-store', [$tool]),
            );

        return view('content.launch-creator', [
            'tool' => $tool,
            'launch' => $launch,
        ]);
    }

    public function ltiStore(
        LtiTool $tool,
        DeepLinkingReturnRequest $request,
        ContentItemsMapperInterface $mapper,
    ): View {
        $item = $mapper->map($request->input('content_items'))[0];

        $version = DB::transaction(function () use ($item, $tool) {
            $content = new Content();
            $content->save();

            $version = ContentVersion::makeFromLtiContentItem($item, $tool, $this->getUser());
            $content->versions()->save($version);

            $content->users()->save($this->getUser(), [
                'role' => ContentUserRole::Owner,
            ]);

            return $version;
        });
        assert($version instanceof ContentVersion);

        $content = $version->content;
        assert($content instanceof Content);

        // return to platform consuming Edlib
        if ($request->session()->get('lti.lti_message_type') === 'ContentItemSelectionRequest') {
            $ltiRequest = $content->toItemSelectionRequest();

            return view('lti.redirect', [
                'url' => $ltiRequest->getUrl(),
                'method' => $ltiRequest->getMethod(),
                'parameters' => $ltiRequest->toArray(),
                'target' => '_parent',
            ]);
        }

        // return to Edlib
        return view('lti.redirect', [
            'url' => route('content.version-details', [$content, $version]),
            'method' => 'GET',
            'target' => '_parent',
        ]);
    }

    public function ltiUpdate(
        LtiTool $tool,
        Content $content,
        DeepLinkingReturnRequest $request,
        ContentItemsMapperInterface $mapper,
    ): View {
        $item = $mapper->map($request->input('content_items'))[0];
        assert($item instanceof LtiLinkItem);

        $version = ContentVersion::makeFromLtiContentItem($item, $tool, $this->getUser());
        $content->versions()->save($version);

        // return to platform consuming Edlib
        if ($request->session()->get('lti.lti_message_type') === 'ContentItemSelectionRequest') {
            $ltiRequest = $content->toItemSelectionRequest();

            return view('lti.redirect', [
                'url' => $ltiRequest->getUrl(),
                'method' => $ltiRequest->getMethod(),
                'parameters' => $ltiRequest->toArray(),
                'target' => '_parent',
            ]);
        }

        // return to Edlib
        return view('lti.redirect', [
            'url' => route('content.version-details', [$content, $version]),
            'method' => 'GET',
            'target' => '_parent',
        ]);
    }

    public function sitemap(): Response
    {
        $document = Content::generateSiteMap();

        return new Response($document->saveXML(), headers: [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function layoutSwitch(): RedirectResponse
    {
        match(Session::get('contentLayout', 'grid')) {
            'grid' => Session::put('contentLayout', 'list'),
            default => Session::put('contentLayout', 'grid')
        };

        return redirect()->back();
    }
}
