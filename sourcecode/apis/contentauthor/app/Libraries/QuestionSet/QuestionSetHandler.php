<?php

namespace App\Libraries\QuestionSet;


use App\Content;
use App\Events\QuestionsetWasSaved;
use App\Events\ResourceSaved;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\QuestionSet;
use App\QuestionSetQuestion;
use App\QuestionSetQuestionAnswer;
use Cerpus\QuestionBankClient\QuestionBankClient;
use Cerpus\VersionClient\VersionData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class QuestionSetHandler
{

    /**
     * @param $values
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function store($values, Request $request)
    {
        /** @var QuestionSet $questionSet */
        $questionSet = QuestionSet::make();
        $questionSet->title = $values['title'];
        $questionSet->owner = Session::get('authId');
        $questionSet->language_code = $request->session()->get('locale', '');
        $questionSet->is_published = $questionSet::isUserPublishEnabled() ? $request->input('isPublished', 1) : 1;
        $questionSet->license = $request->get('license', '');
        $questionSet->is_draft = $request->input('isDraft', 0);
        if ($questionSet->save() !== true) {
            throw new \Exception("Could not store Question Set");
        };

        $collaborators = explode(',', $request->input('col-emails', ''));
        $questionSet->setCollaborators($collaborators)->notifyNewCollaborators();

        $this->storeNewQuestionsWithAnswers($questionSet, $values['cards']);

        event(new QuestionsetWasSaved($questionSet, $request, Session::get('authId'), VersionData::CREATE, Session::all()));

        if (!empty($values['selectedPresentation'])) {
            list($id, $title, $type, $score, $fallbackUrl) = $this->createPresentation($values['selectedPresentation'], $request, $questionSet);
            event(new ResourceSaved($questionSet->getEdlibDataObject()));
        } else {
            list($id, $title, $type, $score, $fallbackUrl) = [
                $questionSet->id,
                $questionSet->title,
                "QuestionSet",
                true,
                route('questionset.edit', $questionSet->id)
            ];
        }

        return [$id, $title, $type, $score, $fallbackUrl];
    }


    /**
     * @param QuestionSet $questionSet
     * @param array $questions
     * @throws \Exception
     */
    private function storeNewQuestionsWithAnswers(QuestionSet $questionSet, $questions)
    {
        foreach ($questions as $card) {
            /** @var QuestionSetQuestion $question */
            $question = QuestionSetQuestion::make();
            $question->question_text = QuestionBankClient::stripMathContainer($card['question']['text']);
            $question->image = !empty($card['question']['image']['id']) ? $card['question']['image']['id'] : null;
            $question->order = $card['order'];
            $questionSet->questions()->save($question);

            foreach ($card['answers'] as $answerIndex => $answer) {
                $questionAnswer = QuestionSetQuestionAnswer::make();
                $questionAnswer->answer_text = QuestionBankClient::stripMathContainer($answer['answerText']);
                $questionAnswer->correct = $answer['isCorrect'];
                $questionAnswer->image = !empty($answer['image']['id']) ? $answer['image']['id'] : null;
                $questionAnswer->order = $answerIndex;
                if ($question->answers()->save($questionAnswer) === false) {
                    throw new \Exception("Could not create answer");
                }
            }
        }
    }

    private function createPresentation($selectedPresentation, Request $request, QuestionSet $questionSet)
    {
        /** @var QuestionSetConvert $questionsetConverter */
        $questionsetConverter = app(QuestionSetConvert::class);
        list($id, $title, $machineName, $route, $resourceType) = $questionsetConverter
            ->convert(
                $selectedPresentation,
                $questionSet,
                new ResourceMetadataDataObject(
                    license: $request->get('license'),
                    share: $request->get('share'),
                    tags: $request->get('tags'),
                ),
            );

        return [$id, $title, $machineName, true, $route, $resourceType];
    }


    /**
     * @param QuestionSet $questionSet
     * @param $values
     * @param Request $request
     * @return array
     * @throws \Throwable
     */
    public function update(QuestionSet $questionSet, $values, Request $request)
    {
        $questionSet->title = $values['title'];
        $questionSet->is_published = $questionSet::isUserPublishEnabled() ? $request->input('isPublished', 1) : 1;
        $questionSet->is_draft = $request->input('isDraft', 0);
        $questionSet->license = $request->input('license', $questionSet->license);
        $questionSet->save();

        DB::transaction(function () use ($values, $questionSet) {
            $storeQuestion = function ($question, $newValues) {
                $question->question_text = QuestionBankClient::stripMathContainer($newValues['question']['text']);
                $question->image = !empty($newValues['question']['image']['id']) ? $newValues['question']['image']['id'] : null;
                $question->order = $newValues['order'];
                $question->save();
            };

            $storeAnswer = function ($answer, $newValues) {
                $answer->answer_text = QuestionBankClient::stripMathContainer($newValues['answerText']);
                $answer->correct = (bool)$newValues['isCorrect'];
                $answer->image = !empty($newValues['image']['id']) ? $newValues['image']['id'] : null;
                $answer->order = $newValues['order'];
                $answer->save();
            };

            $questions = collect($values['cards'])
                ->map(function ($question, $index) {
                    $question['order'] = $index;
                    $question['answers'] = collect($question['answers'])
                        ->map(function ($answer, $index) {
                            $answer['order'] = $index;
                            return $answer;
                        })
                        ->toArray();
                    return $question;
                })
                ->keyBy('id');

            $existingQuestions = $questionSet
                ->questions()
                ->get()
                ->keyBy('id')
                ->each(function ($question) use ($questions, $storeQuestion, $storeAnswer) {
                    if ($questions->has($question->id) !== true) {
                        $question->answers()->delete();
                        $question->delete();
                        return;
                    }
                    $storeQuestion($question, $questions[$question->id]);
                    $providedAnswers = collect($questions[$question->id]['answers'])->keyBy('id');
                    $existingAnswers = $question->answers()->get()->keyBy('id');
                    $existingAnswers->each(function ($answer) use ($providedAnswers, $storeAnswer) {
                        if ($providedAnswers->has($answer->id) !== true) {
                            $answer->delete();
                            return;
                        }
                        $storeAnswer($answer, $providedAnswers[$answer->id]);
                    });
                    $providedAnswers
                        ->diffKeys($existingAnswers)
                        ->each(function ($newAnswer) use ($question) {
                            $answer = QuestionSetQuestionAnswer::make();
                            $answer->answer_text = QuestionBankClient::stripMathContainer($newAnswer['answerText']);
                            $answer->correct = $newAnswer['isCorrect'];
                            $answer->image = !empty($newAnswer['image']['id']) ? $newAnswer['image']['id'] : null;
                            $answer->order = $newAnswer['order'];
                            $question->answers()->save($answer);
                        });
                });

            $newQuestions = $questions
                ->diffKeys($existingQuestions)
                ->toArray();

            $this->storeNewQuestionsWithAnswers($questionSet, $newQuestions);
        });

        if ($questionSet->isOwner(Session::get('authId'))) {
            $collaborators = explode(',', $request->input('col-emails', ''));
            $questionSet->setCollaborators($collaborators)->notifyNewCollaborators();
        }

        event(new QuestionsetWasSaved($questionSet, $request, Session::get('authId'), VersionData::UPDATE, Session::all()));

        if (!empty($values['selectedPresentation'])) {
            list($id, $title, $type, $score, $fallbackUrl, $resourceType) = $this->createPresentation($values['selectedPresentation'], $request, $questionSet);
            event(new ResourceSaved($questionSet->getEdlibDataObject()));
        } else {
            list($id, $title, $type, $score, $fallbackUrl, $resourceType) = [
                $questionSet->id,
                $questionSet->title,
                "QuestionSet",
                false,
                route('questionset.edit', $questionSet->id),
                Content::TYPE_QUESTIONSET,
            ];
        }

        return [$id, $title, $type, $score, $fallbackUrl, $resourceType];
    }

}
