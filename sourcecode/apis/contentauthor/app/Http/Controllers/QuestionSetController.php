<?php

namespace App\Http\Controllers;

use App\Gametype;
use App\Http\Libraries\License;
use App\Http\Requests\ApiQuestionsetRequest;
use App\Libraries\DataObjects\EditorConfigObject;
use App\Libraries\DataObjects\QuestionSetStateDataObject;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\Games\Millionaire\Millionaire;
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

use function config;

class QuestionSetController extends Controller
{
    use ReturnToCore;
    use FractalTransformer;

    public function __construct(private readonly Lti $lti)
    {
        $this->middleware('lti.question-set')->only(['create']);
    }

    private function getQuestionsetContentTypes(): Collection
    {
        $contentTypes = collect();
        if (Gametype::ofName(Millionaire::$machineName)->count() > 0) {
            $contentTypes->push([
                'img' => '/graphical/MillionaireIcon.png',
                'label' => trans('game.millionaire-title'),
                'outcome' => Millionaire::$machineName,
            ]);
        }
        return $contentTypes;
    }

    public function create(Request $request): View
    {
        $ltiRequest = $this->lti->getRequest($request);
        $emails = '';
        $contenttypes = $this->getQuestionsetContentTypes();
        $extQuestionSetData = Session::get(SessionKeys::EXT_QUESTION_SET, null);
        Session::forget(SessionKeys::EXT_QUESTION_SET);

        $editorSetup = EditorConfigObject::create([
            'canList' => true,
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'editorLanguage' => Session::get('locale', config('app.fallback_locale')),
        ])->toJson();

        $state = QuestionSetStateDataObject::create([
            'links' => (object) [
                "store" => route('questionset.store'),
            ],
            'questionSetJsonData' => $extQuestionSetData,
            'contentTypes' => $contenttypes,
            'license' => License::getDefaultLicense(),
            'isPublished' => $ltiRequest?->getPublished() ?? false,
            'isShared' => $ltiRequest?->getShared() ?? false,
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('questionset.store'),
            '_method' => "POST",
            'numberOfDefaultQuestions' => 2,
            'numberOfDefaultAnswers' => 2,
        ])->toJson();

        return view('question.create')->with(compact([
            'emails',
            'editorSetup',
            'state',
        ]));
    }

    public function store(ApiQuestionsetRequest $request): JsonResponse
    {
        $questionsetData = json_decode($request->get('questionSetJsonData'), true);

        /** @var QuestionSetHandler $questionsetHandler */
        $questionsetHandler = app(QuestionSetHandler::class);
        $questionSet = $questionsetHandler->store($questionsetData, $request);

        $url = $this->getRedirectToCoreUrl(
            $questionSet->toLtiContent(
                published: $request->validated('isPublished'),
                shared: $request->validated('isShared'),
            ),
            $request->get('redirectToken'),
        );

        return response()->json(['url' => $url], Response::HTTP_CREATED);
    }

    public function edit(Request $request, $id): View
    {
        $ltiRequest = $this->lti->getRequest($request);
        $questionset = QuestionSet::findOrFail($id);

        $links = (object) [
            "store" => route('questionset.store'),
            "self" => route('questionset.update', [
                'questionset' => $questionset->id,
            ]),
        ];

        $this->addIncludeParse('questions.answers');
        $questionSetData = $this->buildItem($questionset, new QuestionSetsTransformer());
        $contenttypes = $this->getQuestionsetContentTypes();
        $emails = $questionset->getCollaboratorEmails();

        $editorSetup = EditorConfigObject::create([
            'canList' => $questionset->canList($request),
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'editorLanguage' => Session::get('locale', config('app.fallback_locale')),
        ]);
        $editorSetup->setContentProperties(ResourceInfoDataObject::create([
            'id' => $questionset->id,
            'createdAt' => $questionset->created_at->toIso8601String(),
            'ownerName' => null,
        ]));

        $editorSetup = $editorSetup->toJson();

        $state = QuestionSetStateDataObject::create([
            'id' => $questionset->id,
            'title' => $questionset->title,
            'license' => $questionset->license,
            'isPublished' => $ltiRequest?->getPublished() ?? false,
            'isDraft' => $questionset->isDraft(),
            'isShared' => $ltiRequest?->getShared() ?? false,
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
            'editorSetup',
        ]));
    }

    public function update(ApiQuestionsetRequest $request, QuestionSet $questionset)
    {
        $ltiRequest = $this->lti->getRequest($request);
        $questionsetData = json_decode($request->get('questionSetJsonData'), true);

        /** @var QuestionSetHandler $questionsetHandler */
        $questionsetHandler = app(QuestionSetHandler::class);
        $questionSet = $questionsetHandler->update(
            $questionset,
            $questionsetData,
            $request,
        );

        $url = $this->getRedirectToCoreUrl($questionSet->toLtiContent(
            published: $ltiRequest?->getPublished() ?? false,
            shared: $ltiRequest?->getShared() ?? false,
        ), $request->get('redirectToken'));

        return response()->json(['url' => $url], Response::HTTP_OK);
    }

    public function show($id)
    {
        $qCount = QuestionSet::findOrFail($id)->questions()->count();
        return trans("questions.preview", ['qCount' => $qCount]);
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
