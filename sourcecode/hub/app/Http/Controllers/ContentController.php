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
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use function assert;
use function is_string;
use function strtolower;
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

    public function details(
        Content $content,
        Request $request,
        LtiLaunchBuilder $launchBuilder,
    ): View {
        $content->trackView($request, ContentViewSource::Detail);

        $version = $content->latestPublishedVersion()->firstOrFail();

        $tool = $version->tool;
        assert($tool instanceof LtiTool);

        $launchUrl = $version->lti_launch_url;
        assert(is_string($launchUrl));

        $launch = $launchBuilder
            ->withWidth(640)
            ->withHeight(480)
            ->toPresentationLaunch($tool, $launchUrl, $content->id);

        return view('content.details', [
            'content' => $content,
            'launch' => $launch,
        ]);
    }

    public function version(
        Content $content,
        ContentVersion $version,
        LtiLaunchBuilder $launchBuilder,
    ): View {
        $launchUrl = $version->lti_launch_url;
        assert(is_string($launchUrl));

        $tool = $version->tool;
        assert($tool instanceof LtiTool);

        $launch = $launchBuilder->toPresentationLaunch(
            $tool,
            $launchUrl,
            $version->id,
        );

        return view('content.details', [
            'content' => $content,
            'version' => $version,
            'launch' => $launch,
        ]);
    }

    public function share(
        Content $content,
        LtiLaunchBuilder $launchBuilder,
        Request $request,
    ): View {
        $content->trackView($request, ContentViewSource::Share);

        $tool = $content->latestPublishedVersion?->tool;
        assert($tool instanceof LtiTool);

        $launchUrl = $content->latestPublishedVersion?->lti_launch_url;
        assert(is_string($launchUrl));

        $launch = $launchBuilder
            ->withWidth(640)
            ->withHeight(480)
            ->toPresentationLaunch($tool, $launchUrl, $content->id . '/share');

        return view('content.share', [
            'content' => $content,
            'launch' => $launch,
        ]);
    }

    public function embed(Content $content, LtiLaunchBuilder $launchBuilder): View
    {
        $tool = $content->latestPublishedVersion?->tool;
        assert($tool instanceof LtiTool);

        $launchUrl = $content->latestPublishedVersion?->lti_launch_url;
        assert(is_string($launchUrl));

        $launch = $launchBuilder
            ->withWidth(640)
            ->withHeight(480)
            ->toPresentationLaunch($tool, $launchUrl, $content->id . '/embed');

        return view('content.embed', [
            'content' => $content,
            'version' => $content->latestPublishedVersion,
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
        $user = $this->getUser();
        $copy = $content->createCopyBelongingTo($user);

        return to_route('content.index', [$copy->id]);
    }

    public function edit(Content $content, LtiLaunchBuilder $builder): View
    {
        $version = $content->latestPublishedVersion ?? abort(404);
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

        $content = DB::transaction(function () use ($item, $tool) {
            $title = $item->getTitle() ?? throw new Exception('Missing title');
            $url = $item->getUrl() ?? throw new Exception('Missing URL');

            $content = new Content();
            $content->save();

            $version = new ContentVersion();
            $version->title = $title;
            $version->lti_tool_id = $tool->id;
            $version->lti_launch_url = $url;
            $version->published = true; // TODO

            if ($item instanceof EdlibLtiLinkItem) {
                $version->language_iso_639_3 = strtolower($item->getLanguageIso639_3() ?? 'und');
                $version->license = $item->getLicense();
            }

            $content->users()->save($this->getUser(), [
                'role' => ContentUserRole::Owner,
            ]);

            $content->versions()->save($version);

            return $content;
        });
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
            'url' => route('content.details', $content),
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

        DB::transaction(function () use ($content, $item, $tool): void {
            $title = $item->getTitle() ?? throw new Exception('Missing title');
            $url = $item->getUrl() ?? throw new Exception('Missing URL');

            $version = new ContentVersion();
            $version->lti_tool_id = $tool->id;
            $version->title = $title;
            $version->lti_launch_url = $url;
            $version->published = true; // TODO

            if ($item instanceof EdlibLtiLinkItem) {
                $version->language_iso_639_3 = strtolower($item->getLanguageIso639_3() ?? 'und');
                $version->license = $item->getLicense();
            }

            $content->versions()->save($version);
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
            'url' => route('content.details', $content),
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

        return Redirect()->back();
    }
}
