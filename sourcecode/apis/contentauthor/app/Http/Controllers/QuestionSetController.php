<?php

namespace App\Http\Controllers;

use App\Content;
use App\Events\ContentCreated;
use App\Events\ContentCreating;
use App\Events\ContentUpdated;
use App\Events\ContentUpdating;
use App\Libraries\DataObjects\EditorConfigObject;
use App\Libraries\DataObjects\QuestionSetStateDataObject;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use Log;
use Session;
use App\H5pLti;
use App\Gametype;
use App\SessionKeys;
use App\QuestionSet;
use App\ACL\ArticleAccess;
use Illuminate\Http\Request;
use App\Traits\ReturnToCore;
use Illuminate\Http\Response;
use App\Http\Libraries\License;
use App\Http\Libraries\LtiTrait;
use App\Traits\FractalTransformer;
use App\Http\Requests\ApiQuestionsetRequest;
use App\Libraries\Games\Millionaire\Millionaire;
use App\Libraries\QuestionSet\QuestionSetHandler;
use App\Transformers\QuestionSetsTransformer;
use function Cerpus\Helper\Helpers\profile as config;

class QuestionSetController extends Controller
{
    use LtiTrait;
    use ReturnToCore;
    use ArticleAccess;
    use FractalTransformer;

    const QUESTIONSET_TMP_IMAGE_FOLDER = 'temp' . DIRECTORY_SEPARATOR . 'images';


    public function __construct(H5pLti $h5pLti)
    {
        $this->lti = $h5pLti;
        $this->middleware('core.auth')->only(['create', 'edit', 'store', 'update']);
        $this->middleware('lti.question-set')->only(['ltiCreate']);
        $this->middleware('questionset-access', ['only' => ['ltiEdit']]);
        $this->middleware('draftaction', ['only' => ['edit', 'update', 'store', 'create']]);

        // This middleware is used to test the pre filling of the question set with values from the LTI request. Uncomment if you need to test.
        // Will only work when APP_ENV=local
        // Enable in .env "FEATURE_EXT_QUESTION_SET_TO_REQUEST=true"
        //$this->middleware('lti.qs-to-request')->only(['create']);
    }

    private function getQuestionsetContentTypes()
    {
        $contentTypes = collect();
        $contentTypes->push([
            'img' => '/h5pstorage/libraries/H5P.QuestionSet-1.13/icon.svg',
            "label" => 'Quiz',
            "outcome" => \App\Libraries\H5P\Packages\QuestionSet::$machineName,
        ]);
        if (Gametype::ofName(Millionaire::$machineName)->get()->isNotEmpty()) {
            $contentTypes->push([
                'img' => '/h5pstorage/libraries/H5P.QuestionSet-1.13/icon.svg',
                "label" => 'Millionaire',
                "outcome" => Millionaire::$machineName,
            ]);
        }
        return $contentTypes;
    }

    public function create(Request $request)
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        $jwtTokenInfo = $request->session()->get('jwtToken', null);
        $jwtToken = $jwtTokenInfo && isset($jwtTokenInfo['raw']) ? $jwtTokenInfo['raw'] : null;

        $emails = '';

        $licenseLib = new License(config('license'), config('cerpus-auth.key'), config('cerpus-auth.secret'));
        $contenttypes = $this->getQuestionsetContentTypes();
        $extQuestionSetData = Session::get(SessionKeys::EXT_QUESTION_SET, null);
        Session::forget(SessionKeys::EXT_QUESTION_SET);

        $editorSetup = EditorConfigObject::create([
                'useDraft' => true,
                'canPublish' => true,
                'canList' => true,
                'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            ]
        )->toJson();

        $state = QuestionSetStateDataObject::create([
                'links' => (object)[
                    "store" => route('questionset.store')
                ],
                'questionSetJsonData' => $extQuestionSetData,
                'contentTypes' => $contenttypes,
                'license' => $licenseLib->getDefaultLicense(),
                'isPublished' => false,
                'share' => config('h5p.defaultShareSetting'),
                'redirectToken' => $request->get('redirectToken'),
                'route' => route('questionset.store'),
                '_method' => "POST",
            ])->toJson();

        return view('question.create')->with(compact([
            'jwtToken',
            'emails',
            'editorSetup',
            'state',
        ]));
    }

    /**
     * @param ApiQuestionsetRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ApiQuestionsetRequest $request)
    {
        event(new ContentCreating($request));

        $questionsetData = json_decode($request->get('questionSetJsonData'), true);

        try {
            $questionsetHandler = app(QuestionSetHandler::class);
            [$id, $title, $type, $score, $fallbackUrl] = $questionsetHandler->store($questionsetData, $request);
        } catch (\Exception $exception) {
            return response()->json([
                'text' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        event(new ContentCreated(Content::findContentById($id)));

        $urlToCore = $this->getRedirectToCoreUrl(
            $id,
            $title,
            $type,
            $score,
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL

        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : $fallbackUrl
        ];

        return response()->json($responseValues, Response::HTTP_CREATED);
    }

    public function edit(Request $request, $id)
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        /** @var QuestionSet $questionset */
        $questionset = QuestionSet::findOrFail($id);
        $questionset->tags = $questionset->getMetaTagsAsString();

        $jwtTokenInfo = $request->session()->get('jwtToken', null);
        $jwtToken = $jwtTokenInfo && isset($jwtTokenInfo['raw']) ? $jwtTokenInfo['raw'] : null;

        $licenseLib = new License(config('license'), config('cerpus-auth.key'), config('cerpus-auth.secret'));

        $isPrivate = !$questionset->isPublished();

        $links = (object)[
            "store" => route('questionset.store'),
            "self" => route('questionset.update', [
                'questionset' => $questionset->id,
            ]),
        ];

        $this->addIncludeParse('questions.answers');
        $questionSetData = $this->buildItem($questionset, new QuestionSetsTransformer);
        $contenttypes = $this->getQuestionsetContentTypes();
        $emails = $questionset->getCollaboratorEmails();
        $ownerName = $questionset->getOwnerName($questionset->owner);

        $adapter = app(H5PAdapterInterface::class);
        $useDraft = $adapter->enableDraftLogic();

        $editorSetup = EditorConfigObject::create([
                'useDraft' => $useDraft,
                'canPublish' => $questionset->canPublish($request),
                'canList' => $questionset->canList($request),
                'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            ]
        );
        $editorSetup->setContentProperties(ResourceInfoDataObject::create([
            'id' => $questionset->id,
            'createdAt' => $questionset->created_at->toIso8601String(),
            'ownerName' => !empty($ownerName) ? $ownerName : null,
        ]));

        $editorSetup = $editorSetup->toJson();

        $state = QuestionSetStateDataObject::create([
            'id' => $questionset->id,
            'title' => $questionset->title,
            'license' => $licenseLib->getLicense($id),
            'isPublished' => !$questionset->inDraftState(),
            'share' => !$questionset->isPublished() ? 'private' : 'share',
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('questionset.update', ['questionset' => $id]),
            '_method' => "PUT",
            'links' => $links,
            'contentTypes' => $contenttypes,
            'questionset' => $questionSetData,
        ])->toJson();

        return view('question.edit')->with(compact([
            'jwtToken',
            'emails',
            'emails',
            'state',
            'editorSetup'
        ]));
    }

    public function update(ApiQuestionsetRequest $request, QuestionSet $questionset)
    {
        event(new ContentUpdating($questionset, $request));

        if (!$this->canCreate()) {
            abort(403);
        }
        $questionsetData = json_decode($request->get('questionSetJsonData'), true);
        try {
            $questionsetHandler = app(QuestionSetHandler::class);
            [$id, $title, $type, $score, $fallbackUrl] = $questionsetHandler->update($questionset, $questionsetData, $request);
        } catch (\Exception $exception) {
            Log::error($exception->getFile() . ' (' . $exception->getLine() . '): ' . $exception->getMessage());

            return response()->json([
                'text' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getFile() . ' (' . $throwable->getLine() . '): ' . $throwable->getMessage());

            return response()->json([
                'text' => $throwable->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $content = QuestionSet::find($id);

        if (isset($content)) {
            event(new ContentUpdated(QuestionSet::find($id)), null);
        }

        $urlToCore = $this->getRedirectToCoreUrl(
            $id,
            $title,
            $type,
            $score,
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL

        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : $fallbackUrl
        ];

        return response()->json($responseValues, Response::HTTP_OK);

    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }

    public function doShow($id, $context, $preview = false)
    {
        return "Nothing to see. Moooooove along!";
    }

    public function setQuestionImage(Request $request)
    {
        $file = $request->file('file');
        $image = \ImageService::store($file->getPathname());
        if ($image->state === 'finished') {
            return response()->json(['file' => $image->id]);
        }
        return response()->json(['error' => "Could not store file"], Response::HTTP_BAD_REQUEST);
    }
}
