<?php

namespace App\Listeners\Questionset;

use App\Events\QuestionsetWasSaved;
use Cerpus\QuestionBankClient\DataObjects\AnswerDataObject;
use Cerpus\QuestionBankClient\DataObjects\MetadataDataObject;
use Cerpus\QuestionBankClient\DataObjects\QuestionDataObject;
use Cerpus\QuestionBankClient\DataObjects\QuestionsetDataObject;
use Cerpus\QuestionBankClient\QuestionBankClient;

class HandleQuestionbank
{
    protected $questionset;
    protected $tags;

    public function handle(QuestionsetWasSaved $event)
    {
        if (!config("questionbank-client.enabled")) {
            return;
        }

        $request = $event->request;
        if ($request->filled('selectedPresentation') !== true) {
            return;
        }

        $this->tags = $request->get('tags');
        $this->questionset = $event->questionset->fresh(['questions.answers']);
        $this->storeQuestionset();
        $this->storeQuestionsWithAnswers();
    }

    private function storeQuestionset()
    {
        $questionsetDataObject = QuestionsetDataObject::create([
            'id' => $this->questionset->external_reference,
            'title' => $this->questionset->title,
        ]);
        if (!empty($this->tags)) {
            $questionsetDataObject->addMetadata(MetadataDataObject::create([
                'keywords' => is_array($this->tags) ? $this->tags : explode(",", $this->tags),
            ]));
        }

        $questionbankQuestionset = QuestionBankClient::storeQuestionset($questionsetDataObject);
        $this->questionset->external_reference = $questionbankQuestionset->id;
        $this->questionset->save();
    }

    private function storeQuestionsWithAnswers()
    {
        $this->questionset->questions->each(function ($question) {
            $questionDataObject = QuestionDataObject::create([
                'id' => $question->external_reference,
                'text' => $question->question_text,
                'questionSetId' => $this->questionset->external_reference,
            ]);
            $metadata = MetadataDataObject::create();
            if (!empty($this->tags)) {
                $metadata->keywords = is_array($this->tags) ? $this->tags : explode(",", $this->tags);
            }

            if (!empty($question->image)) {
                $metadata->images = [$question->image];
            }

            if ($metadata->isDirty()) {
                $questionDataObject->addMetadata($metadata);
            }

            $questionbankQuestion = QuestionBankClient::storeQuestion($questionDataObject);
            $question->external_reference = $questionbankQuestion->id;
            $question->save();
            $question->answers->each(function ($answer) use ($question) {
                $answerDataObject = AnswerDataObject::create([
                    'id' => $answer->external_reference,
                    'text' => $answer->answer_text,
                    'isCorrect' => $answer->correct,
                    'questionId' => $question->external_reference,
                ]);
                $metadata = MetadataDataObject::create();
                if (!empty($this->tags)) {
                    $metadata->keywords = is_array($this->tags) ? $this->tags : explode(",", $this->tags);
                }

                if (!empty($answer->image)) {
                    $metadata->images = [$answer->image];
                }

                if ($metadata->isDirty()) {
                    $answerDataObject->addMetadata($metadata);
                }

                $questionbankAnswer = QuestionBankClient::storeAnswer($answerDataObject);
                $answer->external_reference = $questionbankAnswer->id;
                $answer->save();
            });
        });
    }
}
