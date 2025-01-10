<?php

namespace App\Http\Middleware;

use App\Lti\Lti;
use App\SessionKeys;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Validator;

class LtiQuestionSet
{
    public function __construct(private readonly Lti $lti) {}

    /**
     * Extract Question Set data from a LTI request, validate, add order property and add to session.
     */
    public function handle(Request $request, Closure $next)
    {
        $ltiRequest = $this->lti->getRequest($request);

        if ($ltiRequest && $ltiRequest->getExtQuestionSet()) {
            // In the LTI request the question set is a base64 encoded json string in the property "ext_question_set"
            $extQuestionSet = json_decode(base64_decode($ltiRequest->getExtQuestionSet()));

            $validator = Validator::make(json_decode(json_encode($extQuestionSet), true), [
                'title' => 'required|string',
                'tags' => 'sometimes|array',
                'questions' => 'required|array',
                'questions.*.text' => 'required',
                'questions.*.answers' => 'required|array',
                'questions.*.answers.*.text' => 'required',
                'questions.*.answers.*.correct' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                // If the validation fails, log the errors, but keep going as this is not a show stopper.
                // The "only" consequence is that users will not have a pre-filled QuestionSet editor
                Log::error(
                    "Validation of ext_question_set LTI param failed. Errors:",
                    $validator->messages()->getMessages(),
                );

                // In case we have a stale value from another request we just remove the key from the session
                Session::forget(SessionKeys::EXT_QUESTION_SET);
            } else {
                $extQuestionSetWithOrderProperty = $this->addOrderPropertyToQuestionSet($extQuestionSet);
                Session::put(SessionKeys::EXT_QUESTION_SET, json_encode($extQuestionSetWithOrderProperty));
            }
        }

        return $next($request);
    }

    private function addOrderPropertyToQuestionSet($extQuestionSet)
    {
        $questionOrder = 1;
        foreach ($extQuestionSet->questions as $question) {
            $question->order = $questionOrder++;

            $answerOrder = 1;
            foreach ($question->answers as $answer) {
                $answer->order = $answerOrder++;
            }
        }

        return $extQuestionSet;
    }
}
