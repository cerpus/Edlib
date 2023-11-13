<?php

namespace App\Http\Controllers;

use App\ACL\ArticleAccess;
use App\Gametype;
use App\H5PLibrary;
use App\Http\Libraries\License;
use App\Http\Libraries\LtiTrait;
use App\Http\Requests\ApiQuestionsetRequest;
use App\Libraries\DataObjects\EditorConfigObject;
use App\Libraries\DataObjects\QuestionSetStateDataObject;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\Games\Millionaire\Millionaire;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Packages\QuestionSet as QuestionSetPackage;
use App\Libraries\QuestionSet\QuestionSetHandler;
use App\Lti\Lti;
use App\QuestionSet;
use App\SessionKeys;
use App\Traits\FractalTransformer;
use App\Traits\ReturnToCore;
use App\Transformers\QuestionSetsTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

use function Cerpus\Helper\Helpers\profile as config;

class QuestionSetController extends Controller
{
    use LtiTrait;
    use ReturnToCore;
    use ArticleAccess;
    use FractalTransformer;

    public function __construct(private readonly Lti $lti)
    {
        $this->middleware('lti.verify-auth')->only(['create', 'edit', 'store', 'update']);
        $this->middleware('lti.question-set')->only(['ltiCreate']);
        $this->middleware('questionset-access', ['only' => ['ltiEdit']]);
    }

    private function getQuestionsetContentTypes(): Collection
    {
        $contentTypes = collect();
        if (
            H5PLibrary::fromMachineName(QuestionSetPackage::$machineName)
            ->version(QuestionSetPackage::$majorVersion, QuestionSetPackage::$minorVersion)
            ->count() > 0
        ) {
            $contentTypes->push([
                'img' => '/graphical/QuizIcon.png',
                'label' => 'Question Set (H5P)',
                'outcome' => QuestionSetPackage::$machineName,
            ]);
        }
        if (Gametype::ofName(Millionaire::$machineName)->count() > 0) {
            $contentTypes->push([
                'img' => '/graphical/MillionaireIcon.png',
                'label' => 'Millionaire mini game',
                'outcome' => Millionaire::$machineName,
            ]);
        }
        return $contentTypes;
    }

    public function create(Request $request): View
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        $emails = '';
        $contenttypes = $this->getQuestionsetContentTypes();
        $extQuestionSetData = Session::get(SessionKeys::EXT_QUESTION_SET, null);
        Session::forget(SessionKeys::EXT_QUESTION_SET);

        $editorSetup = EditorConfigObject::create([
            'userPublishEnabled' => true,
            'canPublish' => true,
            'canList' => true,
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'editorLanguage' => Session::get('locale', config('app.fallback_locale')),
        ])->toJson();

        $state = QuestionSetStateDataObject::create([
            'links' => (object)[
                "store" => route('questionset.store')
            ],
            'questionSetJsonData' => $extQuestionSetData,
            'contentTypes' => $contenttypes,
            'license' => License::getDefaultLicense(),
            'isPublished' => false,
            'share' => config('h5p.defaultShareSetting'),
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('questionset.store'),
            '_method' => "POST",
        ])->toJson();

        return view('question.create')->with(compact([
            'emails',
            'editorSetup',
            'state',
        ]));
    }

    /**
     * @return JsonResponse
     */
    public function store(ApiQuestionsetRequest $request)
    {
        $questionsetData = json_decode($request->get('questionSetJsonData'), true);

        /** @var QuestionSetHandler $questionsetHandler */
        $questionsetHandler = app(QuestionSetHandler::class);
        $questionSet = $questionsetHandler->store($questionsetData, $request);

        $url = $this->getRedirectToCoreUrl($questionSet->toLtiContent(), $request->get('redirectToken'));

        return response()->json(['url' => $url], Response::HTTP_CREATED);
    }

    public function edit(Request $request, $id): View
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        $questionset = QuestionSet::findOrFail($id);

        $links = (object)[
            "store" => route('questionset.store'),
            "self" => route('questionset.update', [
                'questionset' => $questionset->id,
            ]),
        ];

        $this->addIncludeParse('questions.answers');
        $questionSetData = $this->buildItem($questionset, new QuestionSetsTransformer());
        $contenttypes = $this->getQuestionsetContentTypes();
        $emails = $questionset->getCollaboratorEmails();
        $ownerName = $questionset->getOwnerName($questionset->owner);

        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);

        $editorSetup = EditorConfigObject::create([
            'userPublishEnabled' => $adapter->isUserPublishEnabled(),
            'canPublish' => $questionset->canPublish($request),
            'canList' => $questionset->canList($request),
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'editorLanguage' => Session::get('locale', config('app.fallback_locale')),
        ]);
        $editorSetup->setContentProperties(ResourceInfoDataObject::create([
            'id' => $questionset->id,
            'createdAt' => $questionset->created_at->toIso8601String(),
            'ownerName' => !empty($ownerName) ? $ownerName : null,
        ]));

        $editorSetup = $editorSetup->toJson();

        $state = QuestionSetStateDataObject::create([
            'id' => $questionset->id,
            'title' => $questionset->title,
            'license' => $questionset->license,
            'isPublished' => $questionset->isPublished(),
            'isDraft' => $questionset->isDraft(),
            'share' => !$questionset->isListed() ? 'private' : 'share',
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('questionset.update', ['questionset' => $id]),
            '_method' => "PUT",
            'links' => $links,
            'contentTypes' => $contenttypes,
            'questionset' => $questionSetData,
        ])->toJson();

        return view('question.edit')->with(compact([
            'emails',
            'emails',
            'state',
            'editorSetup'
        ]));
    }

    public function update(ApiQuestionsetRequest $request, QuestionSet $questionset)
    {
        if (!$this->canCreate()) {
            abort(403);
        }
        $questionsetData = json_decode($request->get('questionSetJsonData'), true);

        /** @var QuestionSetHandler $questionsetHandler */
        $questionsetHandler = app(QuestionSetHandler::class);
        $questionSet = $questionsetHandler->update(
            $questionset,
            $questionsetData,
            $request
        );

        $url = $this->getRedirectToCoreUrl($questionSet->toLtiContent(), $request->get('redirectToken'));

        return response()->json(['url' => $url], Response::HTTP_OK);
    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }

    public function doShow($id, $context, $preview = false)
    {
        return trans("questions.preview");
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
