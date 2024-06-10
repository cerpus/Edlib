<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContentUserRole;
use App\Enums\ContentViewSource;
use App\Enums\LtiToolEditMode;
use App\Http\Requests\ContentStatusRequest;
use App\Http\Requests\DeepLinkingReturnRequest;
use App\Http\Requests\ContentFilter;
use App\Lti\LtiLaunchBuilder;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use Cerpus\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function is_string;
use function redirect;
use function response;
use function route;
use function to_route;
use function trans;
use function view;

class ContentController extends Controller
{
    public function index(ContentFilter $request): View
    {
        $query = Content::findShared($request->getQuery());
        $request->applyCriteria($query);

        return view($request->ajax() ? 'content.hx-index' : 'content.index', [
            'contents' => $query->paginate(),
            'filter' => $request,
        ]);
    }

    public function mine(ContentFilter $request): View
    {
        $request->setForUser();
        $query = Content::findForUser($this->getUser(), $request->getQuery());
        $request->applyCriteria($query);

        return view($request->ajax() ? 'content.hx-mine' : 'content.mine', [
            'contents' => $query->paginate(),
            'filter' => $request,
        ]);
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

    public function preview(
        Content $content,
        ContentVersion $version,
        Request $request,
    ): View {
        if (!$request->ajax()) {
            abort(400);
        }

        return view('content.hx-preview', [
            'content' => $content,
            'version' => $version,
            'launch' => $version->toLtiLaunch(),
        ]);
    }

    public function history(Content $content): View
    {
        return view('content.history', [
            'content' => $content,
            'versions' => $content->versions()->paginate(),
        ]);
    }

    public function roles(Content $content): View
    {
        return view('content.roles', [
            'content' => $content,
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

        return to_route('content.version-details', [$copy, $copy->latestVersion]);
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
            route('content.lti-update', [$tool, $content, $version]),
        );

        return view('content.edit', [
            'content' => $content,
            'version' => $version,
            'launch' => $launch,
        ]);
    }

    public function updateStatus(Content $content, ContentStatusRequest $request): Response
    {
        $content->shared = $request->contentIsShared();
        $content->save();

        if ($request->ajax()) {
            return response()->noContent();
        }

        return redirect()->back();
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

    public function delete(Content $content, Request $request): Response|RedirectResponse
    {
        DB::transaction($content->delete(...));

        $request->session()
            ->flash('alert', trans('messages.alert-content-deleted'));

        if ($request->ajax()) {
            return response()->noContent()
                ->header('HX-Redirect', route('content.mine'));
        }

        return redirect()->route('content.mine');
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
            $content->saveQuietly();
            $content->users()->save($this->getUser(), [
                'role' => ContentUserRole::Owner,
            ]);
            $version = $content->createVersionFromLinkItem($item, $tool, $this->getUser());
            $content->save();

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
        ContentVersion $version,
        DeepLinkingReturnRequest $request,
        ContentItemsMapperInterface $mapper,
    ): View {
        $item = $mapper->map($request->input('content_items'))[0];
        assert($item instanceof LtiLinkItem);

        $version = DB::transaction(function () use ($content, $version, $item, $tool) {
            $previousVersion = $version;

            $version = $content->createVersionFromLinkItem($item, $tool, $this->getUser());
            $version->previousVersion()->associate($previousVersion);
            $version->save();

            return $version;
        });

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
        $xml = Content::generateSiteMap()->saveXML();
        assert(is_string($xml));

        return new Response($xml, headers: [
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
