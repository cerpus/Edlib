<?php

namespace App\Http\Controllers;

use App\ACL\ArticleAccess;
use App\H5pLti;
use App\Http\Libraries\EmbedlyService;
use App\Http\Libraries\License;
use App\Http\Libraries\LtiTrait;
use App\Http\Requests\EmbedRequest;
use App\Libraries\DataObjects\EmbedStateDataObject;
use App\Link;
use App\Traits\ReturnToCore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmbedController extends Controller implements LtiTypeInterface
{
    use LtiTrait;
    use ReturnToCore;
    use ArticleAccess;

    public function __construct(private H5pLti $lti)
    {
        $this->middleware('core.return', ['only' => ['create']]);
        $this->middleware('core.auth', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('core.locale', ['only' => ['create', 'edit', 'store', 'update']]);
    }

    public function create(Request $request): View
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        $ltiRequest = $this->lti->getValidatedLtiRequest();

        $licenses = License::getLicenses($ltiRequest);
        $license = License::getDefaultLicense($ltiRequest);
        $emails = '';
        $link = new Link();
        $redirectToken = $request->get('redirectToken');
        $userPublishEnabled = false;
        $canPublish = true;
        $canList = true;

        $state = EmbedStateDataObject::create([
            'license' => $license,
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('embed.store'),
            '_method' => "POST",
        ])->toJson();

        return view('embed.create')->with(compact([
            'licenses',
            'license',
            'emails',
            'link',
            'redirectToken',
            'userPublishEnabled',
            'canPublish',
            'canList',
            'state',
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(EmbedRequest $request)
    {
        if (!$this->canCreate()) {
            abort(403);
        }
        $inputs = $request->all();
        $url = $inputs['link'];

        $response = EmbedlyService::get($url);
        if ($response == null) {
            throw ValidationException::withMessages([
                "link" => "not found"
            ]);
        }

        $urlToCore = $this->getCoreBaseUrl($request->get('redirectToken'));

        $responseValues = [
            'url' => $urlToCore . "?" . http_build_query([
                    "return_type" => "link",
                    "url" => $inputs['link'],
                ])
        ];

        return response()->json($responseValues, Response::HTTP_CREATED);
    }

    public function doShow($id, $context, $preview = false): View
    {
        abort(501, 'Not available for this type');
    }

    public function edit(Request $request, $id): View
    {
        abort(501, 'Not available for this type');
    }
}
