<?php

namespace App\Http\Controllers;

use App\ACL\ArticleAccess;
use App\ContentVersion;
use App\Events\LinkWasSaved;
use App\Http\Libraries\License;
use App\Http\Libraries\LtiTrait;
use App\Http\Requests\LinksRequest;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Link;
use App\Lti\Lti;
use App\Traits\ReturnToCore;
use Carbon\Carbon;
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

    public function __construct(private readonly Lti $lti)
    {
        $this->middleware('core.return', ['only' => ['create', 'edit']]);
        $this->middleware('lti.verify-auth', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('core.locale', ['only' => ['create', 'edit', 'store', 'update']]);
    }

    public function create(Request $request)
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);
        $ltiRequest = $this->lti->getRequest($request);

        $licenses = License::getLicenses($ltiRequest);
        $license = License::getDefaultLicense($ltiRequest);

        $emails = '';
        $link = new Link();
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
        if (!$this->canCreate()) {
            abort(403);
        }

        $inputs = $request->all();
        $metadata = json_decode($inputs['linkMetadata']);

        $link = new Link();
        $link->link_type = $inputs['linkType'];
        $link->link_url = $inputs['linkUrl'];
        $link->owner_id = Session::get('authId');
        $link->link_text = !empty($inputs['linkText']) ? $inputs['linkText'] : null;
        $link->title = $metadata->title;
        $link->metadata = !empty($inputs['linkMetadata']) ? $inputs['linkMetadata'] : null;
        $link->is_published = $link::isUserPublishEnabled() ? $request->input('isPublished', 1) : 1;
        $link->license = $inputs['license'] ?? '';
        $link->save();

        event(new LinkWasSaved($link, ContentVersion::PURPOSE_CREATE));

        $url = $this->getRedirectToCoreUrl($link->toLtiContent(), $request->get('redirectToken'));

        return response()->json(['url' => $url], Response::HTTP_CREATED);
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
                $lockMessage = trans(
                    'lock.article-will-expire',
                    [
                        'expires' => $expires->diffInMinutes($now),
                        'editor' => $locked->getEditor(),
                    ]
                );
                $editUrl = route('link.edit', $id);
                $pollUrl = route('lock.status', $id);

                return view('content-lock.locked')->with(compact('lockHeadline', 'lockMessage', 'editUrl', 'pollUrl'));
            } else {
                $link->lock();
            }
        }

        $emails = ""; //$this->getCollaboratorsEmails($link);
        $ltiRequest = $this->lti->getRequest($request);
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
        $link = new Link();
        $oldLink = $link::findOrFail($id);

        if (!$this->canCreate()) {
            abort(403);
        }

        $inputs = $request->all();

        $oldLicense = $oldLink->getContentLicense();
        $reason = $oldLink->shouldCreateFork(Session::get('authId', false)) ? ContentVersion::PURPOSE_COPY : ContentVersion::PURPOSE_UPDATE;

        if ($reason === ContentVersion::PURPOSE_COPY && !$request->input("license", false)) {
            $request->merge(["license" => $oldLicense]);
        }

        // If you are a collaborator, use the old license
        if ($oldLink->isCollaborator()) {
            $request->merge(["license" => $oldLicense]);
        }

        $link = $oldLink;
        if ($oldLink->requestShouldBecomeNewVersion($request)) {
            switch ($reason) {
                case ContentVersion::PURPOSE_UPDATE:
                    $link = $oldLink->makeCopy();
                    break;
                case ContentVersion::PURPOSE_COPY:
                    $link = $oldLink->makeCopy(Session::get('authId'));
                    break;
            }
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

        $url = $this->getRedirectToCoreUrl($link->toLtiContent(), $request->get('redirectToken'));

        return response()->json(['url' => $url], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function doShow($id, $context): View
    {
        $customCSS = $this->lti->getRequest(request())?->getLaunchPresentationCssUrl();
        /** @var Link $link */
        $link = Link::findOrFail($id);

        $metadata = !is_null($link->metadata) ? json_decode($link->metadata) : null;

        return view('link.show')->with(compact('link', 'customCSS', 'metadata'));
    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }
}
