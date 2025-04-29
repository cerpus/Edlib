<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataObjects\LtiCreateInfo;
use App\Enums\ContentRole;
use App\Enums\ContentViewSource;
use App\Enums\LtiToolEditMode;
use App\Http\Requests\AddContextToContentRequest;
use App\Http\Requests\ContentStatisticsRequest;
use App\Http\Requests\ContentStatusRequest;
use App\Http\Requests\DeepLinkingReturnRequest;
use App\Http\Requests\ContentFilter;
use App\Lti\ContentItemSelectionFactory;
use App\Lti\LtiLaunchBuilder;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\Context;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use BadMethodCallException;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
            'contents' => $request->paginateWithModel($query),
            'filter' => $request,
        ]);
    }

    public function mine(ContentFilter $request): View
    {
        $request->setForUser();
        $query = Content::findForUser($this->getUser(), $request->getQuery());
        $request->applyCriteria($query);

        return view($request->ajax() ? 'content.hx-mine' : 'content.mine', [
            'contents' => $request->paginateWithModel(
                $query,
                forUser: true,
                showDrafts: true,
            ),
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

    public function shareDialog(Content $content, Request $request): View
    {
        if (!$request->header('HX-Request')) {
            abort(400);
        }

        return view('content.hx-share-dialog', [
            'content' => $content,
        ]);
    }

    public function embed(Content $content, ContentVersion|null $version = null): View
    {
        $version ??= $content->latestPublishedVersion()->firstOrFail();
        $launch = $version->toLtiLaunch();

        return view('content.embed', [
            'content' => $content,
            'version' => $version,
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
        // @phpstan-ignore larastan.noUnnecessaryCollectionCall
        $availableContexts = Context::all()
            ->diff($content->contexts)
            ->mapWithKeys(fn(Context $context) => [$context->id => $context->name]);

        return view('content.roles', [
            'content' => $content,
            'available_contexts' => $availableContexts,
        ]);
    }

    public function addContext(Content $content, AddContextToContentRequest $request): RedirectResponse
    {
        $context = Context::where('id', $request->validated('context'))
            ->firstOrFail();

        $content->contexts()->attach($context);

        return redirect()->back()
            ->with('alert', trans('messages.context-added-to-content'));
    }

    public function removeContext(Content $content, Context $context): RedirectResponse
    {
        $content->contexts()->detach($context->id);

        return redirect()->back()
            ->with('alert', trans('messages.context-removed-from-content'));
    }

    public function create(): View
    {
        $tools = LtiTool::all();

        /** @var LtiCreateInfo[] $info */
        $info = [];

        foreach ($tools as $type) {
            $info[] = LtiCreateInfo::fromLtiTool($type);

            foreach ($type->extras()->forAdmins(false)->get() as $extra) {
                $info[] = LtiCreateInfo::fromLtiToolExtra($type, $extra);
            }
        }

        return view('content.create', [
            'types' => $info,
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
            $version,
        );

        return view('content.edit', [
            'content' => $content,
            'version' => $version,
            'launch' => $launch,
        ]);
    }

    public function publish(Content $content, ContentVersion $version, Request $request): Response
    {
        $version->published = true;
        $version->save();

        return redirect()->back()->with('alert', trans('messages.content-published-notice'));
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

    public function use(
        Content $content,
        ContentVersion $version,
        Request $request,
        ContentItemSelectionFactory $itemSelectionFactory,
    ): View {
        $returnUrl = $request->session()->get('lti.content_item_return_url')
            ?? throw new BadMethodCallException('Not in LTI selection context');
        assert(is_string($returnUrl));

        $platform = LtiPlatform::where('key', $request->session()->get('lti.oauth_consumer_key'))->firstOrFail();

        $ltiRequest = $itemSelectionFactory->createItemSelection(
            [$version->toLtiLinkItem($platform)],
            $returnUrl,
            $platform->getOauth1Credentials(),
            $request->session()->get('lti.data'),
        );

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

    public function launchCreator(
        LtiTool $tool,
        LtiToolExtra|null $extra,
        LtiLaunchBuilder $launchBuilder,
    ): View {
        $launch = $launchBuilder
            ->withWidth(640)
            ->withHeight(480)
            ->toItemSelectionLaunch(
                $tool,
                $extra->lti_launch_url ?? $tool->creator_launch_url,
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

        if (
            $request->session()->get('lti.lti_message_type') === 'ContentItemSelectionRequest' &&
            $item instanceof EdlibLtiLinkItem
        ) {
            // force inserted content to be published
            $item = $item->withPublished(true);
        }

        $version = DB::transaction(function () use ($item, $tool) {
            $content = new Content();
            $content->saveQuietly();
            $content->users()->save($this->getUser(), [
                'role' => ContentRole::Owner,
            ]);
            $version = $content->createVersionFromLinkItem($item, $tool, $this->getUser());

            if ($item instanceof EdlibLtiLinkItem && $item->isShared() !== null) {
                $content->shared = $item->isShared();
            }

            $content->save();

            return $version;
        });
        assert($version instanceof ContentVersion);

        $content = $version->content;
        assert($content instanceof Content);

        // return to platform consuming Edlib
        if ($request->session()->get('lti.lti_message_type') === 'ContentItemSelectionRequest') {
            $ltiRequest = $version->toItemSelectionRequest();

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

        $user = $this->getUser();

        if ($request->session()->get('lti.ext_edlib3_copy_before_save') === '1') {
            $content = $content->createCopyBelongingTo($user, $version);
            $version = $content->latestVersion;
        }

        if (
            $request->session()->get('lti.lti_message_type') === 'ContentItemSelectionRequest' &&
            $item instanceof EdlibLtiLinkItem
        ) {
            // force inserted content to be published
            $item = $item->withPublished(true);
        }

        $version = DB::transaction(function () use ($content, $version, $item, $tool, $user) {
            $previousVersion = $version;

            $version = $content->createVersionFromLinkItem($item, $tool, $user);
            $version->previousVersion()->associate($previousVersion);
            $version->save();

            if ($item instanceof EdlibLtiLinkItem && $item->isShared() !== null) {
                $content->shared = $item->isShared();
                $content->save();
            }

            return $version;
        });
        assert($version instanceof ContentVersion);

        // return to platform consuming Edlib
        if ($request->session()->get('lti.lti_message_type') === 'ContentItemSelectionRequest') {
            $ltiRequest = $version->toItemSelectionRequest();

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
        match (Session::get('contentLayout', 'grid')) {
            'grid' => Session::put('contentLayout', 'list'),
            default => Session::put('contentLayout', 'grid'),
        };

        return redirect()->back();
    }

    public function statistics(ContentStatisticsRequest $request, Content $content): View|JsonResponse
    {
        $graph = $content->buildStatsGraph(
            $request->getStartDate(),
            $request->getEndDate(),
        );

        if ($request->ajax()) {
            return response()->json([
                'values' => $graph->getData(),
                'formats' => $request->getDateFormatsForResolution(),
            ]);
        }

        return view('content.statistics', [
            'content' => $content,
            'graph' => [
                'values' => $graph->getData(),
                'groups' => $request->dataGroups(),
                'defaultHiddenGroups' => $request->dataGroups()->flip()->except(['total'])->keys(),
                'texts' => [
                    'title' => trans('messages.number-of-views'),
                    'emptyData' => trans('messages.chart-no-data'),
                    'resetButton' => trans('messages.reset-zoom'),
                    'groupNames' =>
                        $request->dataGroups()
                            ->mapWithKeys(function ($item) {
                                return [$item => trans("messages.view-$item")];
                            }),
                    'loading' => trans('messages.loading'),
                    'loadingFailed' => trans('messages.chart-load-error'),
                ],
                'formats' => $request->getDateFormatsForResolution($graph->inferResolution()),
            ],
        ]);
    }

    /**
     * @throws \App\Exceptions\ContentLockedException
     */
    public function refreshLock(Content $content, Request $request): Response
    {
        // no locking when making a copy
        if (!$request->session()->get('lti.ext_edlib3_copy_before_save')) {
            $content->refreshLock($this->getUser());
        }

        return response()->noContent();
    }

    public function releaseLock(Content $content): Response
    {
        $content->releaseLock($this->getUser());

        return response()->noContent();
    }
}
