<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\API\Transformers\AnswerTransformer;
use App\Http\Controllers\API\Transformers\QuestionsetTransformer;
use App\Http\Controllers\API\Transformers\QuestionTransformer;
use App\Http\Controllers\Controller;
use App\Traits\FractalTransformer;
use Cerpus\QuestionBankClient\DataObjects\SearchDataObject;
use Cerpus\QuestionBankClient\QuestionBankClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuestionsetController extends Controller
{
    use FractalTransformer;

    public function getQuestionsets(Request $request)
    {
        $questionsetResults = collect();
        if ($request->filled('title')) {
            $titleResults = QuestionBankClient::getQuestionsets(SearchDataObject::create('search', $request->get('title')), false);
            $questionsetResults->prepend($titleResults);
        }
        if ($request->filled('tags')) {
            $tagsResult = QuestionBankClient::getQuestionsets(SearchDataObject::create('keyword', $request->get('tags')), false);
            $questionsetResults->prepend($tagsResult);
        }

        if ($request->filled('include')) {
            $this->addIncludeParse($request->get('include'));
        }

        $questionsets = $questionsetResults
            ->flatten()
            ->unique('id')
            ->shuffle()
            ->take(20);

        return $this->buildCollectionResponse($questionsets, new QuestionsetTransformer);
    }

    public function getQuestionset($questionsetId)
    {
        try {
            $questionset = QuestionBankClient::getQuestionset($questionsetId);
            $this->addIncludeParse('questions');
            return $this->buildItemResponse($questionset, new QuestionsetTransformer);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function getQuestions($questionsetId)
    {
        try {
            $questionset = QuestionBankClient::getQuestions($questionsetId, true);
            return $this->buildCollectionResponse($questionset, new QuestionTransformer());
        } catch (\Exception $exception) {
            return response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function searchAnswers(Request $request)
    {
        try {
            $searchResults = collect();
            if ($request->filled('title')) {
                $titleResults = QuestionBankClient::searchAnswers(SearchDataObject::create('search', $request->get('title')));
                $searchResults->prepend($titleResults);
            }
            if ($request->filled('tags')) {
                $tagsResult = QuestionBankClient::searchAnswers(SearchDataObject::create('keyword', $request->get('tags')));
                $searchResults->prepend($tagsResult);
            }

            if ($request->filled('include')) {
                $this->addIncludeParse($request->get('include'));
            }

            $answers = $searchResults
                ->flatten()
                ->unique('id')
                ->shuffle()
                ->take(100);

            if ($request->filled('onlyWrong')) {
                $answers = $answers->filter(function ($answer) {
                    return $answer->isCorrect === false;
                });
            }
            return $this->buildCollectionResponse($answers, new AnswerTransformer);
        } catch (\Exception $exception) {
        }
    }

    public function searchQuestions(Request $request)
    {
        try {
            $searchResults = collect();
            if ($request->filled('title')) {
                $titleResults = QuestionBankClient::searchQuestions(SearchDataObject::create('search', $request->get('title')));
                $searchResults->prepend($titleResults);
            }
            if ($request->filled('tags')) {
                $tagsResult = QuestionBankClient::searchQuestions(SearchDataObject::create('keyword', $request->get('tags')));
                $searchResults->prepend($tagsResult);
            }

            $questions = $searchResults
                ->flatten()
                ->unique('id')
                ->shuffle()
                ->take(100);

            return $this->buildCollectionResponse($questions, new QuestionTransformer);
        } catch (\Exception $exception) {
        }
    }


}
