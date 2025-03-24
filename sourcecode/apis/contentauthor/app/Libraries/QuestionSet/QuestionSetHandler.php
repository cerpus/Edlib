<?php

namespace App\Libraries\QuestionSet;

use App\Content;
use App\ContentVersion;
use App\Events\QuestionsetWasSaved;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\QuestionSet;
use App\QuestionSetQuestion;
use App\QuestionSetQuestionAnswer;
use Cerpus\QuestionBankClient\QuestionBankClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class QuestionSetHandler
{
    /**
     * @throws \Exception
     */
    public function store($values, Request $request): Content
    {
        $qsData = [
            'title' => $values['title'],
            'language_code' => $request->session()->get('locale', ''),
            'license' => $request->get('license', ''),
            'is_draft' => $request->input('isDraft', 0),
            'tags' => implode(',', $request->get('tags', [])),
        ];

        if (!empty($values['selectedPresentation'])) {
            $qsData['owner'] = Session::get('authId');
            $qsData['cards'] = $values['cards'];

            return $this->createPresentation($values['selectedPresentation'], $request, $qsData);
        }

        $questionSet = new QuestionSet($qsData);
        $questionSet->owner = Session::get('authId');

        if ($questionSet->save() !== true) {
            throw new \Exception("Could not store Question Set");
        }

        $this->storeNewQuestionsWithAnswers($questionSet, $values['cards']);
        $collaborators = explode(',', $request->input('col-emails', ''));
        $questionSet->setCollaborators($collaborators);

        event(new QuestionsetWasSaved($questionSet, $request, Session::get('authId'), ContentVersion::PURPOSE_CREATE, Session::all()));

        return $questionSet;
    }

    /**
     * @throws \Exception
     */
    private function storeNewQuestionsWithAnswers(QuestionSet $questionSet, array $questions): void
    {
        foreach ($questions as $card) {
            $question = new QuestionSetQuestion();
            $question->question_text = QuestionBankClient::stripMathContainer($card['question']['text']);
            $question->image = !empty($card['question']['image']['id']) ? $card['question']['image']['id'] : null;
            $question->order = $card['order'];
            $questionSet->questions()->save($question);

            foreach ($card['answers'] as $answerIndex => $answer) {
                $questionAnswer = new QuestionSetQuestionAnswer();
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

    private function createPresentation($selectedPresentation, Request $request, QuestionSet|array $questionSet): Content
    {
        return app(QuestionSetConvert::class)->convert(
            $selectedPresentation,
            $questionSet,
            new ResourceMetadataDataObject(
                license: $request->get('license'),
                tags: $request->get('tags'),
            ),
        );
    }

    /**
     * @throws \Throwable
     */
    public function update(QuestionSet $questionSet, $values, Request $request): Content
    {
        $questionSet->title = $values['title'];
        $questionSet->is_draft = $request->input('isDraft', 0);
        $questionSet->license = $request->input('license', $questionSet->license);
        $questionSet->tags = implode(',', $request->input('tags', []));
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
                $answer->correct = (bool) $newValues['isCorrect'];
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
                            $answer = new QuestionSetQuestionAnswer();
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
            $questionSet->setCollaborators($collaborators);
        }

        event(new QuestionsetWasSaved($questionSet, $request, Session::get('authId'), ContentVersion::PURPOSE_UPDATE, Session::all()));

        if (!empty($values['selectedPresentation'])) {
            return $this->createPresentation($values['selectedPresentation'], $request, $questionSet);
        }

        return $questionSet;
    }
}
