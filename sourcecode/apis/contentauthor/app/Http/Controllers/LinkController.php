<?php

namespace App\Http\Controllers;

use App\ACL\ArticleAccess;
use App\Events\ContentCreated;
use App\Events\ContentCreating;
use App\Events\ContentUpdated;
use App\Events\ContentUpdating;
use App\Events\LinkWasSaved;
use App\H5pLti;
use App\Http\Libraries\License;
use App\Http\Libraries\LtiTrait;
use App\Http\Requests\LinksRequest;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Link;
use App\Traits\ReturnToCore;
use Carbon\Carbon;
use Cerpus\VersionClient\VersionData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class LinkController extends Controller
{
    use LtiTrait;
    use ReturnToCore;
    use ArticleAccess;

    protected H5pLti $lti;

    public function __construct(H5pLti $h5pLti)
    {
        $this->middleware('core.return', ['only' => ['create', 'edit']]);
        $this->middleware('core.auth', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('core.locale', ['only' => ['create', 'edit', 'store', 'update']]);

        $this->lti = $h5pLti;
    }

    public function create(Request $request)
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);
        $ltiRequest = $this->lti->getLtiRequest();

        $licenses = License::getLicenses($ltiRequest);
        $license = License::getDefaultLicense($ltiRequest);

        $emails = '';
        $link = app(Link::class);
        $redirectToken = $request->get('redirectToken');
        $userPublishEnabled = $adapter->isUserPublishEnabled();
        $canPublish = true;
        $isPublished = false;
        $canList = true;

        return view('link.create')->with(compact('licenses', 'license', 'emails', 'link', 'redirectToken', 'userPublishEnabled', 'canPublish', 'isPublished', 'canList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LinksRequest $request): JsonResponse
    {
        event(new ContentCreating($request));

        if (!$this->canCreate()) {
            abort(403);
        }

        $inputs = $request->all();
        $metadata = json_decode($inputs['linkMetadata']);

        /** @var Link $link */
        $link = app(Link::class);
        $link->link_type = $inputs['linkType'];
        $link->link_url = $inputs['linkUrl'];
        $link->owner_id = Session::get('authId');
        $link->link_text = !empty($inputs['linkText']) ? $inputs['linkText'] : null;
        $link->title = $metadata->title;
        $link->metadata = !empty($inputs['linkMetadata']) ? $inputs['linkMetadata'] : null;
        $link->is_published = $link::isUserPublishEnabled() ? $request->input('isPublished', 1) : 1;
        $link->license = $inputs['license'] ?? '';
        $link->save();

        event(new LinkWasSaved($link, VersionData::CREATE));

        event(new ContentCreated($link));

        $urlToCore = $this->getRedirectToCoreUrl(
            $link->id,
            $link->title,
            'Link',
            $link->givesScore(),
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL

        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : route('link.edit', $link->id),
        ];

        return response()->json($responseValues, Response::HTTP_CREATED);
    }

    public function edit(Request $request, $id)
    {
        $link = Link::findOrFail($id);
        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);

        $isOwner = $link->isOwner(Session::get('authId', 'qawsed'));

        if (!$link->shouldCreateFork(Session::get('authId', false))) {
            $locked = $link->hasLock();
            if ($locked) { // Article is locked, add some info to the response
                $now = Carbon::now();
                $expires = Carbon::createFromTimestamp($locked->updated_at->timestamp)->addHour();
                $lockHeadline = trans('lock.article-is-locked');
                $lockMessage = trans('lock.article-will-expire',
                    [
                        'expires' => $expires->diffInMinutes($now),
                        'editor' => $locked->getEditor(),
                    ]);
                $editUrl = route('link.edit', $id);
                $pollUrl = route('lock.status', $id);

                return view('content-lock.locked')->with(compact('lockHeadline', 'lockMessage', 'editUrl', 'pollUrl'));
            } else {
                $link->lock();
            }
        }

        $emails = ""; //$this->getCollaboratorsEmails($link);
        $ltiRequest = $this->lti->getLtiRequest();
        $licenses = License::getLicenses($ltiRequest);
        $license = $link->license;
        $redirectToken = $request->get('redirectToken');
        $userPublish = $adapter->isUserPublishEnabled();
        $canPublish = $link->canPublish($request);
        $isPublished = $link->is_published;
        $canList = $link->canList($request);

        return view('link.edit')->with(compact('link', 'isOwner', 'emails', 'license', 'licenses', 'id', 'redirectToken', 'userPublish', 'canPublish', 'canList', 'isPublished'));
    }

    public function update(LinksRequest $request, $id)
    {
        /** @var Link $link */
        $link = app(Link::class);
        $oldLink = $link::findOrFail($id);

        event(new ContentUpdating($oldLink, $request));

        if (!$this->canCreate()) {
            abort(403);
        }

        $inputs = $request->all();

        $oldLicense = $oldLink->getContentLicense();
        $reason = $oldLink->shouldCreateFork(Session::get('authId', false)) ? VersionData::COPY : VersionData::UPDATE;

        if ($reason === VersionData::COPY && !$request->input("license", false)) {
            $request->merge(["license" => $oldLicense]);
        }

        // If you are a collaborator, use the old license
        if ($oldLink->isCollaborator()) {
            $request->merge(["license" => $oldLicense]);
        }

        $link = $oldLink;
        if ($oldLink->requestShouldBecomeNewVersion($request)) {
            switch ($reason) {
                case VersionData::UPDATE:
                    $link = $oldLink->makeCopy();
                    break;
                case VersionData::COPY:
                    $link = $oldLink->makeCopy(Session::get('authId'));
                    break;

            }
            $link->setParentId($oldLink->version_id);
        }

        $metadata = json_decode($inputs['linkMetadata']);
        $link->link_url = $inputs['linkUrl'];
        $link->link_text = !empty($inputs['linkText']) ? $inputs['linkText'] : null;
        $link->title = $metadata->title;
        $link->metadata = !empty($inputs['linkMetadata']) ? $inputs['linkMetadata'] : null;
        $link->is_published = $link::isUserPublishEnabled() ? $request->input('isPublished', 1) : 1;
        $link->license = $inputs['license'] ?? $oldLink->license;

        $link->save();

        event(new LinkWasSaved($link, $reason));

        event(new ContentUpdated($link));

        $urlToCore = $this->getRedirectToCoreUrl(
            $link->id,
            $link->title,
            'Link',
            $link->givesScore(),
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL

        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : route('link.edit', $link->id),
        ];

        return response()->json($responseValues, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function doShow($id, $context, $preview = false): View
    {
        $customCSS = !empty($this->lti->getLtiRequest()) ? $this->lti->getLtiRequest()->getLaunchPresentationCssUrl() : null;
        /** @var Link $link */
        $link = Link::findOrFail($id);
        if (!$link->canShow($preview)) {
            return view('layouts.draft-resource', [
                'styles' => !is_null($customCSS) ? [$customCSS] : [],
            ]);
        }

        $metadata = !is_null($link->metadata) ? json_decode($link->metadata) : null;

        return view('link.show')->with(compact('link', 'customCSS', 'metadata'));
    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }
}
