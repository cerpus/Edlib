<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Session;
use App\SessionKeys;

class AddExtQuestionSetToRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (App::environment('local') && config('feature.add-ext-question-set-to-request')) {
            $extQuestionSet = json_decode(base64_decode("eyJ0aXRsZSI6IlF1ZXN0aW9uU2V0IGNyZWF0ZWQgYXQgMjAxOC0wNy0xMFQwNzoyNjoyNCswMjowMCIsInNoYXJpbmciOmZhbHNlLCJsaWNlbnNlIjoiQlkiLCJhdXRoSWQiOiJzb21lLWF1dGgtaWQiLCJ0YWdzIjpbInRhZzEiLCJ0YWcyIl0sInF1ZXN0aW9ucyI6W3sidGV4dCI6IkhvdyBhcmUgeW91PyIsImFuc3dlcnMiOlt7InRleHQiOiJGaW5lIiwiY29ycmVjdCI6dHJ1ZX0seyJ0ZXh0IjoiU28sIHNvLi4uIiwiY29ycmVjdCI6ZmFsc2V9LHsidGV4dCI6IkhvcnJpYmxlISIsImNvcnJlY3QiOmZhbHNlfV19LHsidGV4dCI6IldoZXJlIGFyZSB5b3U/IiwiYW5zd2VycyI6W3sidGV4dCI6IkF0IHdvcmsiLCJjb3JyZWN0Ijp0cnVlfSx7InRleHQiOiJIb21lIiwiY29ycmVjdCI6ZmFsc2V9LHsidGV4dCI6Ik9uIHRoZSBidXMiLCJjb3JyZWN0IjpmYWxzZX1dfV19"));
            $extQuestionSet = $this->addRequiredFieldsToQuestionSet($extQuestionSet);
            Session::put(SessionKeys::EXT_QUESTION_SET, json_encode($extQuestionSet));
        }
        return $next($request);
    }

    private function addRequiredFieldsToQuestionSet($extQuestionSet)
    {
        $questionOrder = 0;

        foreach ($extQuestionSet->questions as $question) {
            $question->order = $questionOrder++;

            $answerOrder = 0;
            foreach ($question->answers as $answer) {
                $answer->order = $answerOrder++;
            }
        }

        return $extQuestionSet;
    }
}
